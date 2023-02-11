<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    use HasFactory;

    public $fillable = [
        'command',
        'response',
        'enabled',
        'cooldown',
        'global_cooldown',
        'type',
        'usable_by',
    ];

    public $casts = [
        'enabled' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeRegular(Builder $query): Builder
    {
        return $query->where("type", "regular");
    }

    public function scopePunishable(Builder $query): Builder
    {
        return $query->where("type", "punishable");
    }

    public function scopeSpecial(Builder $query): Builder
    {
        return $query->where("type", "special");
    }

    public function getSubscriberCanUseAttribute()
    {
        return $this->usable_by === "subscriber"
            || $this->usable_by === "everyone";
    }

    public function getEveryoneCanUseAttribute()
    {
        return $this->usable_by === "everyone";
    }
}
