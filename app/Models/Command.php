<?php

namespace App\Models;

use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Command extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    use BroadcastsEvents;

    public $fillable = [
        'command',
        'response',
        'enabled',
        'cooldown',
        'global_cooldown',
        'type',
        'usable_by',
        'severity',
        'punish_reason',
        'action',
        'prepend_sender',
        'auto_post_enabled',
        'auto_post_id',
    ];

    public $casts = [
        'enabled' => 'boolean',
        'severity' => 'integer',
        'cooldown' => 'integer',
        'global_cooldown' => 'integer',
        'prepend_sender' => 'boolean',
        'auto_post_enabled' => 'boolean',
    ];

    public function punishes(): HasMany
    {
        return $this->hasMany(Punish::class);
    }

    public function autoPost(): BelongsTo
    {
        return $this->belongsTo(AutoPost::class);
    }

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model>
     */
    public function broadcastOn(string $event): array
    {
        return [$this];
    }

    /**
     * Create a new broadcastable model event for the model. Custom to not broadcast to current user
     */
    protected function newBroadcastableEvent(string $event): BroadcastableModelEventOccurred
    {
        return (new BroadcastableModelEventOccurred(
            $this,
            $event
        ))->dontBroadcastToCurrentUser();
    }
}
