<?php

namespace App\Http\Controllers;

use App\Enums\BotStatus;
use App\Http\Requests\UpdateBotSettingsRequest;
use App\Jobs\Analysis\AnalyzeCapsJob;
use App\Jobs\Analysis\AnalyzeEmotesJob;
use App\Models\Command;
use App\Models\Setting;
use App\Services\TwitchWebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Pennant\Feature;
use Throwable;

class BotController extends Controller
{
    public function bot(): Response
    {
        Gate::authorize('moderate');

        $autoCapsCommand = Setting::key('punishment.autoCapsCommand')->first();
        $autoMaxEmotesCommand = Setting::key('punishment.maxEmotesCommand')->first();

        $isRunning = TwitchWebhookService::connect()->isSubscribedToWebhook('channel.chat.message');

        return Inertia::render('Bot/Settings', [
            'botStatus' => $isRunning ? BotStatus::RUNNING : BotStatus::STOPPED,

            'announceRestart' => Feature::active('announce-restart'),
            'punishableBansEnabled' => Feature::active('bans'),
            'punishableTimeoutsEnabled' => Feature::active('timeouts'),
            'punishDebugEnabled' => Feature::active('punish-debug'),

            'autoCapsEnabled' => Feature::active('auto-caps-punishment'),
            'autoBanBots' => Feature::active('auto-ban-bots'),
            'punishableCommands' => Command::query()->punishable()->select(['id', 'command', 'response'])->get(),

            'autoCapsCommand' => $autoCapsCommand ? ((int) $autoCapsCommand->value) : null,
            'autoCapsTotalCapsThreshold' => (float) (Setting::query()->key('punishment.totalCapsThreshold')->first()?->value ?? AnalyzeCapsJob::TOTAL_CAPS_THRESHOLD_DEFAULT),
            'autoCapsTotalLengthThreshold' => (int) (Setting::query()->key('punishment.totalLengthThreshold')->first()?->value ?? AnalyzeCapsJob::TOTAL_LENGTH_THRESHOLD_DEFAULT),
            'autoCapsWordCapsThreshold' => (float) (Setting::query()->key('punishment.wordCapsThreshold')->first()?->value ?? AnalyzeCapsJob::WORD_CAPS_THRESHOLD_DEFAULT),
            'autoCapsWordLengthThreshold' => (int) (Setting::query()->key('punishment.wordLengthThreshold')->first()?->value ?? AnalyzeCapsJob::WORD_LENGTH_THRESHOLD_DEFAULT),

            'autoMaxEmotesEnabled' => Feature::active('auto-max-emotes-punishment'),
            'autoMaxEmotesCommand' => $autoMaxEmotesCommand ? ((int) $autoMaxEmotesCommand->value) : null,
            'autoMaxEmotes' => (int) (Setting::query()->key('punishment.maxEmotes')->first()?->value ?? AnalyzeEmotesJob::MAX_EMOTES_DEFAULT),
        ]);
    }

    public function updateSettings(UpdateBotSettingsRequest $request): RedirectResponse
    {
        Gate::authorize('moderate');

        $validated = $request->validated();

        if ($validated['announceRestart'] ?? null) {
            Feature::activate('announce-restart');
        } else {
            Feature::deactivate('announce-restart');
        }

        if ($validated['punishableBansEnabled'] ?? null) {
            Feature::activate('bans');
        } else {
            Feature::deactivate('bans');
        }

        if ($validated['punishableTimeoutsEnabled'] ?? null) {
            Feature::activate('timeouts');
        } else {
            Feature::deactivate('timeouts');
        }

        if ($validated['punishDebugEnabled'] ?? null) {
            Feature::activate('punish-debug');
        } else {
            Feature::deactivate('punish-debug');
        }

        if ($validated['autoCapsEnabled'] ?? null) {
            Feature::activate('auto-caps-punishment');
        } else {
            Feature::deactivate('auto-caps-punishment');
        }

        if ($validated['autoBanBots'] ?? null) {
            Feature::activate('auto-ban-bots');
        } else {
            Feature::deactivate('auto-ban-bots');
        }

        if (isset($validated['autoCapsCommand'])) {
            Setting::updateOrCreate(['key' => 'punishment.autoCapsCommand'], ['value' => $validated['autoCapsCommand']]);
        }

        if (isset($validated['autoCapsTotalCapsThreshold'])) {
            Setting::updateOrCreate(['key' => 'punishment.totalCapsThreshold'], ['value' => $validated['autoCapsTotalCapsThreshold']]);
        }

        if (isset($validated['autoCapsTotalLengthThreshold'])) {
            Setting::updateOrCreate(['key' => 'punishment.totalLengthThreshold'], ['value' => $validated['autoCapsTotalLengthThreshold']]);
        }

        if (isset($validated['autoCapsWordCapsThreshold'])) {
            Setting::updateOrCreate(['key' => 'punishment.wordCapsThreshold'], ['value' => $validated['autoCapsWordCapsThreshold']]);
        }

        if (isset($validated['autoCapsWordLengthThreshold'])) {
            Setting::updateOrCreate(['key' => 'punishment.wordLengthThreshold'], ['value' => $validated['autoCapsWordLengthThreshold']]);
        }

        if ($validated['autoMaxEmotesEnabled'] ?? null) {
            Feature::activate('auto-max-emotes-punishment');
        } else {
            Feature::deactivate('auto-max-emotes-punishment');
        }

        if (isset($validated['autoMaxEmotesCommand'])) {
            Setting::updateOrCreate(['key' => 'punishment.maxEmotesCommand'], ['value' => $validated['autoMaxEmotesCommand']]);
        }

        if (isset($validated['autoMaxEmotes'])) {
            Setting::updateOrCreate(['key' => 'punishment.maxEmotes'], ['value' => $validated['autoMaxEmotes']]);
        }

        return back()->with('success', 'Bot settings updated');
    }

    public function restart(): RedirectResponse
    {
        Gate::authorize('moderate');

        try {
            $webhookService = TwitchWebhookService::connect();

            try {
                $webhookService->unsubscribeFromWebhook('channel.chat.message');
            } catch (Throwable $th) {
                if ($th->getCode() !== 404) {
                    return back()->with('error', "Something went wrong when trying to unsubscribe from the channel.chat.message webhook. Contact Bjornar97. Error: {$th->getMessage()}");
                }
            }

            $webhookService->subscribeToWebhook('channel.chat.message');

            activity()->log('Restarted bot');
        } catch (Throwable $th) {
            return back()->with('error', "Something went wrong when trying to restart the bot. Contact Bjornar97. Error: {$th->getMessage()}");
        }

        return back()->with('success', 'The bot has been restarted');
    }

    public function start(): RedirectResponse
    {
        Gate::authorize('moderate');

        try {
            TwitchWebhookService::connect()->subscribeToWebhook('channel.chat.message');

            activity()->log('Started bot');
        } catch (\Throwable $th) {
            return back()->with('error', "Something went wrong when trying to start the bot. Contact Bjornar97. Error: {$th->getMessage()}");
        }

        return back()->with('success', 'The bot has been started');
    }

    public function stop(): RedirectResponse
    {
        Gate::authorize('moderate');

        try {
            TwitchWebhookService::connect()->unsubscribeFromWebhook('channel.chat.message');

            activity()->log('Stopped bot');
        } catch (\Throwable $th) {
            return back()->with('error', "Something went wrong when trying to stop the bot. Contact Bjornar97. Error: {$th->getMessage()}");
        }

        return back()->with('success', 'The bot has been stopped');
    }
}
