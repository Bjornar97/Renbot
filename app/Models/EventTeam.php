<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTeam extends Model
{
    public $fillable = [
        'name',
        'color',
        'image_url',
    ];

    /**
     * Get the team that owns the EventTeam
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Event, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
