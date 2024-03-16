<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Streamday extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function streamdaySlots(): HasMany
    {
        return $this->hasMany(StreamdaySlot::class)->orderBy('start_at');
    }
}
