<?php

namespace App\Jobs;

use App\Models\AutoPost;
use App\Models\Command;
use App\Models\Message;
use App\Services\MessageService;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoPostCheckJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public MessageEvent $message)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $messageService = MessageService::message($this->message);

            Message::query()->create([
                'twitch_user_id' => $messageService->getSenderTwitchId(),
                'message' => $this->message->message,
            ]);

            $lastRun = Cache::get("autoPostRun", now()->subHour());

            if ($lastRun->diffInSeconds() < 60) {
                return;
            }

            Cache::put("autoPostRun", now());

            $queues = AutoPost::query()->whereRelation("commands", "auto_post_enabled", true)->get();

            foreach ($queues as $queue) {
                $timeIsRight = now()->sub($queue->interval_type, $queue->interval)->isAfter($queue->last_post);

                if (!$timeIsRight) {
                    continue;
                }

                $postsBetween = Message::query()
                    ->where('created_at', '>', $queue->last_post)
                    ->whereNot("twitch_user_id", config("services.twitch.bot_id", 0))
                    ->count();

                if ($postsBetween < $queue->min_posts_between) {
                    continue;
                }

                $command = $queue->commands()
                    ->active()
                    ->where('auto_post_enabled', true)
                    ->where('id', '>', $queue->last_command_id ?? 0)
                    ->orderBy('id')
                    ->first();

                if (!$command) {
                    $command = $queue->commands()
                        ->active()
                        ->where('auto_post_enabled', true)
                        ->orderBy('id')
                        ->first();
                }

                if (!$command) {
                    Log::debug("No command found");
                    continue;
                }

                $chatMessage = $command->response;
                SingleChatMessageJob::dispatch($chatMessage);

                $queue->lastCommand()->associate($command);
                $queue->last_post = now();
                $queue->save();

                return;
            }
        });
    }
}
