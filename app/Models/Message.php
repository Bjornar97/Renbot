<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public $fillable = [
        'twitch_user_id',
        'message',
        'message_id',
        'irc_recieved_at',
        'webhook_recieved_at',
    ];
}
