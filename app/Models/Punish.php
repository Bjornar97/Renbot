<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Punish extends Model
{
    public $fillable = [
        'twitch_user_id',
        'seconds',
        'type',
    ];

    /**
     * Get the command that owns the Punish
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Command, $this>
     */
    public function command(): BelongsTo
    {
        return $this->belongsTo(Command::class);
    }
}
