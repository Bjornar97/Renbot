<?php

namespace App\Models;

use Database\Factories\StreamdayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamdaySlot extends Model
{
    /** @use HasFactory<StreamdayFactory> */
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

    /**
     * Get the streamday that owns the StreamdaySlot
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Streamday, $this>
     */
    public function streamday(): BelongsTo
    {
        return $this->belongsTo(Streamday::class);
    }

    /**
     * Get the creator that owns the StreamdaySlot
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Creator, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }
}
