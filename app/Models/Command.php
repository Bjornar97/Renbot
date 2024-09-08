<?php

namespace App\Models;

use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'severity' => 'integer',
            'cooldown' => 'integer',
            'global_cooldown' => 'integer',
            'prepend_sender' => 'boolean',
            'auto_post_enabled' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, "parent_id");
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, "parent_id");
    }

    public function punishes(): HasMany
    {
        return $this->hasMany(Punish::class);
    }

    public function autoPost(): BelongsTo
    {
        return $this->belongsTo(AutoPost::class);
    }

    public function commandMetadata(): HasMany
    {
        return $this->hasMany(CommandMetadata::class);
    }


    public function scopeActive(Builder $query): Builder
    {
        return $query->where('enabled', true)->whereDoesntHave('parent', function (Builder $query) {
            return $query->where('enabled', false);
        });
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
        return $this->usable_by === "subscribers"
            || $this->usable_by === "everyone";
    }

    public function getEveryoneCanUseAttribute()
    {
        return $this->usable_by === "everyone";
    }

    public function enabled(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->parent) {
                    return (bool) $this->parent->enabled;
                }

                return (bool) $value;
            },
        );
    }

    public function response(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->parent) {
                    return $this->parent->response;
                }

                return $value;
            },
        );
    }

    public function usableBy(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->parent) {
                    return $this->parent->usable_by;
                }

                return $value;
            },
        );
    }

    public function cooldown(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->parent) {
                    return $this->parent->cooldown;
                }

                return $value;
            },
        );
    }

    public function globalCooldown(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->parent) {
                    return $this->parent->global_cooldown;
                }

                return $value;
            },
        );
    }

    public function severity(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->parent) {
                    return $this->parent->severity;
                }

                return $value;
            },
        );
    }

    public function punishReason(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->parent) {
                    return $this->parent->punish_reason;
                }

                return $value;
            },
        );
    }

    public function action(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->parent) {
                    return $this->parent->action;
                }

                return $value;
            },
        );
    }

    public function prependSender(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->parent) {
                    return (bool) $this->parent->prepend_sender;
                }

                return (bool) $value;
            },
        );
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
