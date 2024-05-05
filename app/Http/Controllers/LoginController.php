<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyPasskeyRequest;
use App\Models\Authenticator;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Socialite\Contracts\User as ContractsUser;
use Laravel\Socialite\Facades\Socialite;
use romanzipp\Twitch\Twitch;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class LoginController extends Controller
{
    // We use this key across several methods, so we're going to define it here
    const CREDENTIAL_REQUEST_OPTIONS_SESSION_KEY = 'publicKeyCredentialRequestOptions';


    public array $regularScopes = [
        'user:read:follows',
        'user:read:subscriptions',
    ];

    public array $moderatorScopes = [
        "moderator:manage:announcements",
        "moderator:manage:automod",
        "moderator:read:automod_settings",
        "moderator:manage:banned_users",
        "moderator:read:blocked_terms",
        "moderator:manage:blocked_terms",
        "moderator:manage:chat_messages",
        "moderator:manage:chat_settings",
        "moderator:read:chatters",
        "moderator:read:followers",
        "moderator:read:shield_mode",
        "moderator:manage:shield_mode",
        "moderator:read:shoutouts",
        "moderator:manage:shoutouts",
        "chat:edit",
        "chat:read"
    ];

    public array $rendogScopes = [
        "moderation:read",
        "bits:read",
        "channel:read:charity",
        "channel:read:polls",
        "channel:manage:polls",
        "channel:read:predictions",
        "channel:manage:predictions",
        "channel:read:redemptions",
        "channel:manage:redemptions",
        "channel:read:editors",
        "channel:read:hype_train",
        "channel:read:subscriptions",
        "channel:read:vips",
        "channel:moderate",
        "channel:manage:schedule",
    ];

    public function redirect(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', 'string'],
            'skipBiometry' => ['nullable', 'string'],
        ]);

        $role = $data['role'];

        $scopes = $this->regularScopes;

        if ($role === 'moderator') {
            $scopes = $this->moderatorScopes;
        }

        if ($role === 'rendog') {
            $scopes = $this->rendogScopes;
        }

        session(['skipBiometry' => $data['skipBiometry'] ?? 'no']);

        return Socialite::driver('twitch')
            ->scopes($scopes)
            ->redirect();
    }

    public function callback()
    {
        $user = Socialite::driver("twitch")->user();

        $accessExpriesAt = now()->addSeconds($user->expiresIn);

        $type = $this->getType($user);

        $user = User::updateOrCreate([
            'twitch_id' => (int) $user->id,
        ], [
            'username' => $user->name,
            'avatar' => $user->avatar,
            'type' => $type,
            'twitch_access_token' => $user->token,
            'twitch_refresh_token' => $user->refreshToken,
            'twitch_access_token_expires_at' => $accessExpriesAt,
        ]);

        if ($user->disabled_at) {
            return redirect()
                ->route("welcome")
                ->with("error", "Your account is unfortunately deactivated, contact Bjornar97 if this is a mistake");
        }

        Auth::login($user);

        $route = "rules";

        if (Gate::allows("moderate")) {
            $route = "commands.index";
        }

        $skipBiometry = session('skipBiometry', 'no');

        if ($skipBiometry === 'yes') {
            return redirect()->intended(route($route))->with("success", "You successfully logged in!");
        }

        return redirect()->route('passkeys.create', [
            'redirect_to' => session('url.intended') ?? route($route),
        ]);
    }

    private function getType(ContractsUser $user)
    {
        $twitch = new Twitch();

        $twitch->withToken($user->token);

        $result = $twitch->getChatters([
            'broadcaster_id' => 30600786,
            'moderator_id' => $user->id,
            'first' => 1,
        ]);

        $type = "viewer";

        if ($result->success() && count($result->data()) > 0) {
            $type = "moderator";
        }

        if (strtolower($user->name) === "rendogtv") {
            $type = "rendog";
        }

        return $type;
    }

    public function createPasskey(Request $request)
    {
        return Inertia::render("Authentication/CreatePasskey", [
            'options' => Inertia::lazy(fn () => $this->getAuthenticationOptions()),
            'intended' => $request->input('redirect_to'),
        ]);
    }

    public function verifyPasskey(VerifyPasskeyRequest $request)
    {
        $data = json_encode($request->validated());

        try {
            // The manager will receive data to load and select the appropriate 
            $attestationStatementSupportManager = AttestationStatementSupportManager::create();
            $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

            $factory = new WebauthnSerializerFactory($attestationStatementSupportManager);
            $serializer = $factory->create();

            /** @var PublicKeyCredential $publicKeyCredential */
            $publicKeyCredential = $serializer->deserialize(
                $data,
                PublicKeyCredential::class,
                'json',
            );

            if (!$publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
                return back()->with("error", "Something went wrong while verifying your device. Please try again or contact Bjornar97");
            }

            $csmFactory = new CeremonyStepManagerFactory();

            if (app()->environment('local')) {
                $csmFactory->setSecuredRelyingPartyId(['localhost']);
            }

            $creationCSM = $csmFactory->creationCeremony();

            // Also valid
            $authenticatorAttestationResponseValidator = AuthenticatorAttestationResponseValidator::create(
                ceremonyStepManager: $creationCSM
            );

            $publicKeyCredentialCreationOptions = session('credential-options');

            if (!$publicKeyCredentialCreationOptions) {
                throw new Exception("Credential options does not exist");
            }

            $publicKeyCredentialSource = $authenticatorAttestationResponseValidator->check(
                $publicKeyCredential->response,
                $publicKeyCredentialCreationOptions,
                $request,
            );

            /** @var User $user */
            $user = auth()->user();
            $user->authenticators()->save(new Authenticator([
                'credential_id' => $publicKeyCredential->id,
                'public_key' => $publicKeyCredentialSource->jsonSerialize(),
            ]));

            return redirect()->route('commands.index')->with('success', "Successfully activated biometry");
        } catch (\Throwable $th) {
            Log::error($th);

            return back()->with('error', "Something went wrong while verifying your device. Please try again or contact Bjornar97");
        }
    }

    public function authenticatePasskey(VerifyPasskeyRequest $request)
    {
        $data = json_encode($request->input());

        // The manager will receive data to load and select the appropriate 
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

        $factory = new WebauthnSerializerFactory($attestationStatementSupportManager);
        $serializer = $factory->create();


        /** @var PublicKeyCredential $publicKeyCredential */
        $publicKeyCredential = $serializer->deserialize(
            $data,
            PublicKeyCredential::class,
            'json',
        );

        if (!$publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            return back()->with('error', "Something went wrong. Try logging in with twitch.");
        }

        $authenticator = Authenticator::where(
            'credential_id',
            $publicKeyCredential->id
        )->first();

        if (!$authenticator) {
            return back()->with('error', "Something went wrong. Try logging in with twitch.");
        }

        $csmFactory = new CeremonyStepManagerFactory();

        if (app()->environment('local')) {
            $csmFactory->setSecuredRelyingPartyId(['localhost']);
        }

        $requestCSM = $csmFactory->requestCeremony();

        $authenticatorAssertionResponseValidator = AuthenticatorAssertionResponseValidator::create(
            ceremonyStepManager: $requestCSM
        );

        $publicKeyCredentialRequestOptions = session('passkey-authenticate-options');

        $publicKeyCredentialSource = $authenticatorAssertionResponseValidator->check(
            PublicKeyCredentialSource::createFromArray($authenticator->public_key),
            $publicKeyCredential->response,
            $publicKeyCredentialRequestOptions,
            $request->host(),
            null,
        );

        session()->forget('passkey-authenticate-options');

        $user = User::query()->find($publicKeyCredentialSource->userHandle)->first();

        Auth::login($user);

        $route = "rules";

        if (Gate::allows("moderate")) {
            $route = "commands.index";
        }

        return redirect()->intended(route($route))->with("success", "You successfully logged in!");
    }

    /**
     * Get passkey authentication options for creating passkey
     */
    private function getAuthenticationOptions()
    {
        /** @var User $user */
        $user = auth()->user();

        $logoPath = public_path("images/rendog-logo.png");
        $logo = "data:image/png;base64," . base64_encode(file_get_contents($logoPath));

        $relyingPartyEntity = PublicKeyCredentialRpEntity::create(
            "Renbot",
            config('auth.passkeys.relying-party-id'),
            $logo,
        );

        $userEntity = PublicKeyCredentialUserEntity::create(
            $user->username,
            $user->id,
            $user->username,
            "data:image/png;base64," . base64_encode(file_get_contents($user->avatar)),
        );

        $challenge = random_bytes(16);

        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::create(
            $relyingPartyEntity,
            $userEntity,
            $challenge,
        );

        session(['user-entity' => $userEntity, 'credential-options' => $publicKeyCredentialCreationOptions]);

        return $publicKeyCredentialCreationOptions;
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return redirect("/")->with("success", "You successfully logged out");
    }
}
