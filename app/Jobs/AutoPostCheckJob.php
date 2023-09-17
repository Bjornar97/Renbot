<?php

namespace App\Jobs;

use App\Events\AutoPostUpdated;
use App\Models\AutoPost;
use App\Models\Message;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        $queues = AutoPost::query()->whereRelation("commands", "auto_post_enabled", true)->get();

        foreach ($queues as $queue) {
            AutoPostUpdated::dispatch($queue);
        }

        DB::transaction(function () {
            // $lastRun = Cache::get("autoPostRun", now()->subHour());

            // if ($lastRun->diffInSeconds() < 60) {
            //     return;
            // }

            // Cache::put("autoPostRun", now());

            $queues = AutoPost::query()
                ->whereRelation("commands", "auto_post_enabled", true)
                ->orderBy('last_post', 'desc')
                ->get();

            foreach ($queues as $queue) {
                $timeIsRight = now()->sub($queue->interval_type, $queue->interval)->isAfter($queue->last_post);

                if (!$timeIsRight) {
                    Log::debug("Time is not right for {$queue->title}");
                    Log::debug($queue->last_post);
                    continue;
                }

                $postsBetween = Message::query()
                    ->where('created_at', '>', $queue->last_post)
                    ->count();

                if ($postsBetween < $queue->min_posts_between) {
                    Log::debug("Not enough chats for {$queue->title}");
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
            }
        });
    }
}
