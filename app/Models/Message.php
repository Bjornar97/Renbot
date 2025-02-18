<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
 * @property array<string,string|null|array<string,string>>|null $fragments
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
}
