<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoPost extends Model
{
    use HasFactory;

    public $fillable = [
        'title',
        'interval',
        'interval_type',
        'min_posts_between',
        'last_post',
    ];

    public $casts = [
        'interval' => 'integer',
        'min_posts_between' => 'integer',
        'last_post' => 'datetime',
    ];

    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }

    public function lastCommand(): BelongsTo
    {
        return $this->belongsTo(Command::class, "last_command_id");
    }
}
