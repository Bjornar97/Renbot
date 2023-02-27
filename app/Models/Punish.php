<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Punish extends Model
{
    use HasFactory;

    public $fillable = [
        'twitch_user_id',
        'seconds',
        'type',
    ];

    public function command(): BelongsTo
    {
        return $this->belongsTo(Command::class);
    }
}
