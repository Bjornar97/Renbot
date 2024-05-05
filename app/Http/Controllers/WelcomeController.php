<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class WelcomeController extends Controller
{
    public function welcome(Request $request)
    {
        $username = $request->input('username');

        if (!auth()->check()) {
            return Inertia::render("Welcome", [
                'passkeyOptions' => Inertia::lazy(fn () => $this->getAuthenticationOptions($username)),
            ]);
        }

        if (Gate::allows("moderate")) {
            return redirect()->route("commands.index");
        }

        return redirect()->route("rules");
    }

    /**
     * Get passkey authentication options for logging in with passkey
     */
    private function getAuthenticationOptions($username)
    {
        $logoPath = public_path("images/rendog-logo.png");
        $logo = "data:image/png;base64," . base64_encode(file_get_contents($logoPath));

        /** @var User $user */
        $user = User::query()->where('username', $username)->firstOrFail();

        $userEntity = PublicKeyCredentialUserEntity::create(
            $user->username,
            $user->id,
            $user->username,
            null,
        );

        // We gather all registered authenticators for this user
        // $publicKeyCredentialSourceRepository corresponds to your own service
        // The purpose of the fictive method findAllForUserEntity is to return all credential source objects
        // registered by the user.
        $registeredAuthenticators = User::with('authenticators')
            ->where('id', $userEntity->id)
            ->first()
            ->authenticators
            ->pluck('public_key')
            ->map(fn ($value) => PublicKeyCredentialSource::createFromArray($value))
            ->toArray();

        // We don’t need the Credential Sources, just the associated Descriptors
        $allowedCredentials = array_map(
            static function (PublicKeyCredentialSource $credential): PublicKeyCredentialDescriptor {
                return $credential->getPublicKeyCredentialDescriptor();
            },
            $registeredAuthenticators
        );

        // Public Key Credential Request Options
        $publicKeyCredentialRequestOptions =
            PublicKeyCredentialRequestOptions::create(
                random_bytes(32), // Challenge
                allowCredentials: $allowedCredentials
            );

        session(['passkey-authenticate-options' => $publicKeyCredentialRequestOptions]);

        return $publicKeyCredentialRequestOptions;
    }
}
