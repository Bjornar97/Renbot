<?php

namespace App\Jobs\Analysis;

use App\Jobs\SingleChatMessageJob;
use App\Models\Command;
use App\Models\Setting;
use App\Services\MessageService;
use App\Services\PunishService;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeCapsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const TOTAL_CAPS_THRESHOLD_DEFAULT = 0.5;

    public const WORD_CAPS_THRESHOLD_DEFAULT = 0.4;

    public const TOTAL_LENGTH_THRESHOLD_DEFAULT = 4;

    public const WORD_LENGTH_THRESHOLD_DEFAULT = 4;

    public const ALL_CAPS_WORD_THRESHOLD_DEFAULT = 3;

    public string $string;

    private MessageService $messageService;

    private float $totalCapsThreshold = self::TOTAL_CAPS_THRESHOLD_DEFAULT;

    private float $wordCapsThreshold = self::WORD_CAPS_THRESHOLD_DEFAULT;

    private int $totalLengthThreshold = self::TOTAL_LENGTH_THRESHOLD_DEFAULT;

    private int $wordLengthThreshold = self::WORD_LENGTH_THRESHOLD_DEFAULT;

    private int $allCapsWordThreshold = self::ALL_CAPS_WORD_THRESHOLD_DEFAULT;

    private ?Command $command;

    /**
     * Create a new job instance.
     */
    public function __construct(MessageEvent $message)
    {
        $this->messageService = MessageService::message($message);
        $this->string = $this->messageService->getMessageWithoutEmotes();
        $this->string = trim($this->string);

        // Remove :ACTION from start of string, since its not part of the message, but added when using /me
        $this->string = preg_replace('/^ACTION /', '', $this->string);

        $this->totalCapsThreshold = Setting::query()->key('punishment.totalCapsThreshold')->first()?->value ?? self::TOTAL_CAPS_THRESHOLD_DEFAULT;
        $this->wordCapsThreshold = Setting::query()->key('punishment.wordCapsThreshold')->first()?->value ?? self::WORD_CAPS_THRESHOLD_DEFAULT;

        $this->totalLengthThreshold = Setting::query()->key('punishment.totalLengthThreshold')->first()?->value ?? self::TOTAL_LENGTH_THRESHOLD_DEFAULT;
        $this->wordLengthThreshold = Setting::query()->key('punishment.wordLengthThreshold')->first()?->value ?? self::WORD_LENGTH_THRESHOLD_DEFAULT;

        $this->command = Command::find(Setting::query()->key('punishment.autoCapsCommand')->first()?->value);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->isPunishable()) {
            return;
        }

        if (! $this->command) {
            return;
        }

        $this->punish();
    }

    protected function punish(): void
    {
        $response = PunishService::user($this->messageService->getSenderTwitchId(), $this->messageService->getSenderDisplayName())
            ->messageId($this->messageService->getMessageId())
            ->command($this->command)
            ->punish();

        if ($response) {
            SingleChatMessageJob::dispatch('chat', $response);
        }
    }

    public function isPunishable(): bool
    {
        if (! $this->hasEnoughCharacters()) {
            return false;
        }

        if ($this->hasTooManyCaps()) {
            return true;
        }

        if ($this->allCapsWordsInARow() >= $this->allCapsWordThreshold) {
            return true;
        }

        if (! $this->hasPunishableWord()) {
            return false;
        }

        return true;
    }

    protected function hasTooManyCaps(): bool
    {
        $caps = preg_match_all('/[A-Z]/', $this->string);
        $total = strlen(preg_replace('/[^a-zA-Z]/', '', $this->string));

        $ratio = $caps / $total;

        if ($ratio > $this->totalCapsThreshold && $caps > $this->totalLengthThreshold) {
            return true;
        }

        return false;
    }

    protected function allCapsWordsInARow(): int
    {
        $maxCount = 0;
        $words = explode(' ', preg_replace('/[^a-zA-Z\s]/', '', $this->string));

        $currentCount = 0;
        foreach ($words as $word) {
            if (strlen($word) > 2 && preg_match('/^[A-Z]+$/', $word)) {
                $currentCount++;
            } else {
                $currentCount = 0;
            }

            if ($currentCount > $maxCount) {
                $maxCount = $currentCount;
            }
        }

        return $maxCount;
    }

    protected function hasEnoughCharacters(): bool
    {
        return strlen($this->string) > $this->totalLengthThreshold;
    }

    protected function hasPunishableWord(): bool
    {
        $words = explode(' ', $this->string);

        foreach ($words as $word) {
            if ($this->isWordPunishable($word)) {
                return true;
            }
        }

        return false;
    }

    protected function isWordPunishable(string $word): bool
    {
        if (! $this->isWordLongEnough($word)) {
            return false;
        }

        if (str_starts_with($word, '@')) {
            return false;
        }

        $caps = preg_match_all('/[A-Z]/', $word);
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
