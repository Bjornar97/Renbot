<?php

namespace App\Jobs\Analysis;

use App\Models\Command;
use App\Services\BotService;
use App\Services\CommandService;
use App\Services\MessageService;
use App\Services\PunishService;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeCapsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const TOTAL_CAPS_THRESHOLD = 0.5;
    public const WORD_CAPS_THRESHOLD = 0.4;

    public const TOTAL_LENGTH_THRESHOLD = 4;
    public const WORD_LENGTH_THRESHOLD = 4;

    public string $string;
    private MessageService $messageService;

    /**
     * Create a new job instance.
     */
    public function __construct(private MessageEvent $message)
    {
        $this->messageService = MessageService::message($message);

        $this->string = $this->messageService->getMessageWithoutEmotes();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->isPunishable()) {
            return;
        }

        $this->punish();
    }

    protected function punish()
    {
        $command = Command::where('command', 'caps')->first();

        PunishService::user($this->messageService->getSenderTwitchId(), $this->messageService->getSenderUsername())
            ->command($command)
            ->bot(BotService::bot())
            ->punish();
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

        if ($ratio > self::TOTAL_CAPS_THRESHOLD && $caps > self::TOTAL_LENGTH_THRESHOLD) {
            return true;
        }

        return false;
    }

    protected function hasEnoughCharacters(): bool
    {
        return strlen($this->string) > self::TOTAL_LENGTH_THRESHOLD;
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

        $caps = preg_match_all("/[A-Z]/", $word);
        $total = strlen($word);
        $ratio = $caps / $total;

        if ($ratio > self::WORD_CAPS_THRESHOLD && $caps > self::WORD_LENGTH_THRESHOLD) {
            return true;
        }

        return false;
    }

    protected function isWordLongEnough(string $word): bool
    {
        return strlen($word) > self::WORD_LENGTH_THRESHOLD;
    }
}
