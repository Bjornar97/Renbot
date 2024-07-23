<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandMetadata extends Model
{
    use HasFactory;

    public $fillable = [
        'type',
        'key',
        'value',
    ];

    public function command(): BelongsTo
    {
        return $this->belongsTo(Command::class);
    }

    public function scopeField(Builder $query): Builder
    {
        return $query->where('type', 'field');
    }
}
