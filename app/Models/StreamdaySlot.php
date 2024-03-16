<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamdaySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_at',
        'end_at',
        'creator_id',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected $with = [
        'creator',
    ];

    public function streamday(): BelongsTo
    {
        return $this->belongsTo(Streamday::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }
}
