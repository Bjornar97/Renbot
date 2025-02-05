<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandMetadata extends Model
{
    public $fillable = [
        'type',
        'key',
        'value',
    ];

    /**
     * Get the command that owns the CommandMetadata
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Command, $this>
     */
    public function command(): BelongsTo
    {
        return $this->belongsTo(Command::class);
    }

    /**
     * Scope a query to only include metadata that is a field.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeField(Builder $query): Builder
    {
        return $query->where('type', 'field');
    }
}
