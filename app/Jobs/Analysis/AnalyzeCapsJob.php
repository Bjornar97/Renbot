<?php

namespace App\Jobs\Analysis;

use App\Models\Command;
use App\Models\Setting;
use App\Services\BotService;
use App\Services\MessageService;
use App\Services\PunishService;
use GhostZero\Tmi\Events\Irc\WelcomeEvent;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeCapsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const TOTAL_CAPS_THRESHOLD_DEFAULT = 0.5;
    public const WORD_CAPS_THRESHOLD_DEFAULT = 0.4;

    public const TOTAL_LENGTH_THRESHOLD_DEFAULT = 4;
    public const WORD_LENGTH_THRESHOLD_DEFAULT = 4;

    public string $string;
    private MessageService $messageService;

    private float $totalCapsThreshold = self::TOTAL_CAPS_THRESHOLD_DEFAULT;
    private float $wordCapsThreshold = self::WORD_CAPS_THRESHOLD_DEFAULT;

    private int $totalLengthThreshold = self::TOTAL_LENGTH_THRESHOLD_DEFAULT;
    private int $wordLengthThreshold = self::WORD_LENGTH_THRESHOLD_DEFAULT;

    private Command|null $command;

    /**
     * Create a new job instance.
     */
    public function __construct(private MessageEvent $message)
    {
        $this->messageService = MessageService::message($message);
        $this->string = $this->messageService->getMessageWithoutEmotes();
        $this->string = trim($this->string);

        // Remove :ACTION from start of string, since its not part of the message, but added when using /me 
        $this->string = preg_replace("/^ACTION /", "", $this->string);

        $this->totalCapsThreshold = Setting::key("punishment.totalCapsThreshold")->first()?->value ?? self::TOTAL_CAPS_THRESHOLD_DEFAULT;
        $this->wordCapsThreshold = Setting::key("punishment.wordCapsThreshold")->first()?->value ?? self::WORD_CAPS_THRESHOLD_DEFAULT;

        $this->totalLengthThreshold = Setting::key("punishment.totalLengthThreshold")->first()?->value ?? self::TOTAL_LENGTH_THRESHOLD_DEFAULT;
        $this->wordLengthThreshold = Setting::key("punishment.wordLengthThreshold")->first()?->value ?? self::WORD_LENGTH_THRESHOLD_DEFAULT;

        $this->command = Command::find(Setting::key("punishment.autoCapsCommand")->first()?->value);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->isPunishable()) {
            return;
        }

        if (!$this->command) {
            return;
        }

        $this->punish();
    }

    protected function punish()
    {
        $bot = BotService::bot();

        $response = PunishService::user($this->messageService->getSenderTwitchId(), $this->messageService->getSenderDisplayName())
            ->command($this->command)
            ->bot($bot)
            ->punish();

        $bot->on(WelcomeEvent::class, function (WelcomeEvent $event) use ($response, $bot) {
            $bot->say(config("services.twitch.channel"), $response);

            $bot->getLoop()->addTimer(3, fn () => $bot->close());
        });
    }

    public function isPunishable(): bool
    {
        if (!$this->hasEnoughCharacters()) {
            return false;
        }

        if ($this->hasTooManyCaps()) {
            return true;
        }

        if (!$this->hasPunishableWord()) {
            return false;
        }

        return true;
    }

    protected function hasTooManyCaps(): bool
    {
        $caps = preg_match_all("/[A-Z]/", $this->string);
        $total = strlen($this->string);
        $ratio = $caps / $total;

        if ($ratio > $this->totalCapsThreshold && $caps > $this->totalLengthThreshold) {
            return true;
        }

        return false;
    }

    protected function hasEnoughCharacters(): bool
    {
        return strlen($this->string) > $this->totalLengthThreshold;
    }

    protected function hasPunishableWord()
    {
        $words = explode(" ", $this->string);

        foreach ($words as $word) {
            if ($this->isWordPunishable($word)) {
                return true;
            }
        }

        return false;
    }

    protected function isWordPunishable(string $word): bool
    {
        if (!$this->isWordLongEnough($word)) {
            return false;
        }

        if (str_starts_with($word, "@")) {
            return false;
        }

        $caps = preg_match_all("/[A-Z]/", $word);
        $total = strlen($word);
        $ratio = $caps / $total;

        if ($ratio > $this->wordCapsThreshold && $caps > $this->wordLengthThreshold) {
            return true;
        }

        return false;
    }

    protected function isWordLongEnough(string $word): bool
    {
        return strlen($word) > $this->wordLengthThreshold;
    }
}
