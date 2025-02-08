<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public $fillable = [
        'twitch_user_id',
        'message',
    ];
}
