<?php

namespace App\Http\Controllers;

use App\Enums\BotStatus;
use App\Http\Requests\UpdateBotSettingsRequest;
use App\Jobs\Analysis\AnalyzeCapsJob;
use App\Jobs\Analysis\AnalyzeEmotesJob;
use App\Models\Command;
use App\Models\Setting;
use App\Services\BotManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Process;
use Inertia\Inertia;
use Laravel\Pennant\Feature;
use Throwable;

class BotController extends Controller
{
    public function bot()
    {
        Gate::authorize("moderate");

        $status = BotManagerService::getStatus();

        $autoCapsCommand = Setting::key("punishment.autoCapsCommand")->first();
        $autoMaxEmotesCommand = Setting::key("punishment.maxEmotesCommand")->first();

        return Inertia::render("Bot/Settings", [
            'botStatus' => $status,

            'announceRestart' => Feature::active("announce-restart"),
            'punishableBansEnabled' => Feature::active("bans"),
            'punishableTimeoutsEnabled' => Feature::active("timeouts"),
            'punishDebugEnabled' => Feature::active("punish-debug"),

            'autoCapsEnabled' => Feature::active("auto-caps-punishment"),
            'autoBanBots' => Feature::active("auto-ban-bots"),
            'punishableCommands' => Command::punishable()->select(['id', 'command', 'response'])->get(),

            'autoCapsCommand' => $autoCapsCommand ? ((int) $autoCapsCommand->value) : null,
            'autoCapsTotalCapsThreshold' => (float) (Setting::key("punishment.totalCapsThreshold")->first()?->value ?? AnalyzeCapsJob::TOTAL_CAPS_THRESHOLD_DEFAULT),
            'autoCapsTotalLengthThreshold' => (int) (Setting::key("punishment.totalLengthThreshold")->first()?->value ?? AnalyzeCapsJob::TOTAL_LENGTH_THRESHOLD_DEFAULT),
            'autoCapsWordCapsThreshold' => (float) (Setting::key("punishment.wordCapsThreshold")->first()?->value ?? AnalyzeCapsJob::WORD_CAPS_THRESHOLD_DEFAULT),
            'autoCapsWordLengthThreshold' => (int) (Setting::key("punishment.wordLengthThreshold")->first()?->value ?? AnalyzeCapsJob::WORD_LENGTH_THRESHOLD_DEFAULT),

            'autoMaxEmotesEnabled' => Feature::active('auto-max-emotes-punishment'),
            'autoMaxEmotesCommand' => $autoMaxEmotesCommand ? ((int) $autoMaxEmotesCommand->value) : null,
            'autoMaxEmotes' => (int) (Setting::key("punishment.maxEmotes")->first()?->value ?? AnalyzeEmotesJob::MAX_EMOTES_DEFAULT),
        ]);
    }

    public function updateSettings(UpdateBotSettingsRequest $request)
    {
        Gate::authorize("moderate");

        $validated = $request->validated();

        if ($validated['announceRestart'] ?? null) {
            Feature::activate("announce-restart");
        } else {
            Feature::deactivate("announce-restart", false);
        }

        if ($validated['punishableBansEnabled'] ?? null) {
            Feature::activate("bans");
        } else {
            Feature::deactivate("bans", false);
        }

        if ($validated['punishableTimeoutsEnabled'] ?? null) {
            Feature::activate("timeouts");
        } else {
            Feature::deactivate("timeouts", false);
        }

        if ($validated['punishDebugEnabled'] ?? null) {
            Feature::activate("punish-debug");
        } else {
            Feature::deactivate("punish-debug", false);
        }

        if ($validated['autoCapsEnabled'] ?? null) {
            Feature::activate("auto-caps-punishment");
        } else {
            Feature::deactivate("auto-caps-punishment", false);
        }

        if ($validated['autoBanBots'] ?? null) {
            Feature::activate("auto-ban-bots");
        } else {
            Feature::deactivate("auto-ban-bots", false);
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
            Feature::activate("auto-max-emotes-punishment");
        } else {
            Feature::deactivate("auto-max-emotes-punishment", false);
        }

        if (isset($validated['autoMaxEmotesCommand'])) {
            Setting::updateOrCreate(['key' => 'punishment.maxEmotesCommand'], ['value' => $validated['autoMaxEmotesCommand']]);
        }

        if (isset($validated['autoMaxEmotes'])) {
            Setting::updateOrCreate(['key' => 'punishment.maxEmotes'], ['value' => $validated['autoMaxEmotes']]);
        }

        return back()->with("success", "Bot settings updated");
    }

    public function restart()
    {
        Gate::authorize("moderate");

        try {
            BotManagerService::restart();
            activity()->log("Restarted bot");
        } catch (Throwable $th) {
            return back()->with("error", "Something went wrong when trying to restart the bot. Contact Bjornar97. Error: {$th->getMessage()}");
        }

        return back()->with("success", "The bot has been restarted");
    }

    public function start()
    {
        Gate::authorize("moderate");

        try {
            BotManagerService::start();
            activity()->log("Started bot");
        } catch (\Throwable $th) {
            return back()->with("error", "Something went wrong when trying to start the bot. Contact Bjornar97. Error: {$th->getMessage()}");
        }

        return back()->with("success", "The bot has been started");
    }

    public function stop()
    {
        Gate::authorize("moderate");

        try {
            BotManagerService::stop();
            activity()->log("Stopped bot");
        } catch (\Throwable $th) {
            return back()->with("error", "Something went wrong when trying to stop the bot. Contact Bjornar97. Error: {$th->getMessage()}");
        }

        return back()->with("success", "The bot has been stopped");
    }
}
