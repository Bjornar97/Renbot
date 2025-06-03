<?php

namespace App\Models;

use App\Jobs\Analysis\AnalyzeCapsJob;
use App\Jobs\Analysis\AnalyzeEmotesJob;
use App\Jobs\SingleChatMessageJob;
use App\Services\CommandService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;

/**
 * App\Models\Message
 *
 * @property int $id
 * @property int $twitch_user_id
 * @property string|null $username
 * @property string|null $display_name
 * @property string|null $user_color
 * @property string|null $message
 * @property string|null $message_id
 * @property array<array<string,string|null|array<string,string>>>|null $fragments
 * @property array<string,string|null>|null $badges
 * @property string|null $reply_to_message_id
 * @property string|null $irc_recieved_at
 * @property string|null $webhook_recieved_at
 */
class Message extends Model
{
    public $fillable = [
        'twitch_user_id',
        'username',
        'display_name',
        'user_color',
        'message',
        'message_id',
        'fragments',
        'badges',
        'reply_to_message_id',
        'irc_recieved_at',
        'webhook_recieved_at',
    ];

    protected function casts(): array
    {
        return [
            'fragments' => 'array',
            'badges' => 'array',
        ];
    }

    protected static function booted()
    {
        static::created(function (Message $message) {
            if ($message->twitch_user_id === config('services.twitch.bot_id')) {
                return;
            }

            Feature::when(
                'auto-caps-punishment',
                whenActive: fn () => AnalyzeCapsJob::dispatch($message),
            );

            Feature::when(
                'auto-max-emotes-punishment',
                whenActive: fn () => AnalyzeEmotesJob::dispatch($message),
            );

            try {
                $commandService = CommandService::message($message);

                $response = $commandService->getResponse();

                if ($response) {
                    SingleChatMessageJob::dispatchSync(
                        'chat',
                        $response
                    );
                }
            } catch (\Throwable $th) {
                Log::error('Error in command service', [
                    'message' => $message->message,
                    'exception' => $th,
                ]);
            }
        });
    }
}
