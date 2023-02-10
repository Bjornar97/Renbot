<?php

namespace App\Jobs;

use App\Models\Command;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Command $command, public string|null $targetUsername, public MessageEvent $message)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->isAuthorized()) {
            DeleteTwitchMessageJob::dispatchSync($this->message->tags['id']);
            return;
        }

        $response = "";

        if ($this->targetUsername) {
            $response .= "@{$this->targetUsername} ";
        }

        $response .= $this->command->response;
    }

    public function isAuthorized()
    {
        $isModerator = (bool) $this->message->tags['mod'];

        if ($isModerator) {
            return true;
        }

        if ($this->command->everyone_can_use) {
            return true;
        }

        $isSubscriber = (bool) $this->message->tags['subscriber'];

        if ($isSubscriber && $this->command->subscriber_can_use) {
            return true;
        }

        return false;
    }
}
