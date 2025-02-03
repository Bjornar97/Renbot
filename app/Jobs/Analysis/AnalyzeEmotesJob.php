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

class AnalyzeEmotesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const MAX_EMOTES_DEFAULT = 5;

    private int $maxEmotes = self::MAX_EMOTES_DEFAULT;

    public string $string;

    private MessageService $messageService;

    private ?Command $command;

    /**
     * Create a new job instance.
     */
    public function __construct(private MessageEvent $message)
    {
        $this->messageService = MessageService::message($message);
        $this->string = $this->messageService->getMessageWithoutEmotes();
        $this->string = trim($this->string);

        // Remove :ACTION from start of string, since its not part of the message, but added when using /me
        $this->string = preg_replace('/^ACTION /', '', $this->string);

        $this->maxEmotes = Setting::key('punishment.maxEmotes')->first()?->value ?? self::MAX_EMOTES_DEFAULT;

        $this->command = Command::find(Setting::key('punishment.maxEmotesCommand')->first()?->value);
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

    protected function punish()
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
        if ($this->hasTooManyEmotes()) {
            return true;
        }

        return false;
    }

    protected function hasTooManyEmotes(): bool
    {
        $emotes = $this->message->tags['emotes'] ?? null;

        if (! $emotes) {
            return false;
        }

        $emotes = explode('/', $emotes);
        $emotesCount = 0;

        foreach ($emotes as $emote) {
            $emotesCount += count(explode(',', $emote));
        }

        if ($emotesCount > $this->maxEmotes) {
            return true;
        }

        return false;
    }
}
