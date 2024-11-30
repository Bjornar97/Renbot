<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTeam extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
        'color',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
