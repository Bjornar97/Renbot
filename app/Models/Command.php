<?php

namespace App\Models;

use App\Services\SpecialCommandService;
use Database\Factories\CommandFactory;
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

/**
 * Class Command
 *
 * @property Command|null $parent
 * @property Command[] $children
 * @property Punish[] $punishes
 * @property AutoPost|null $autoPost
 * @property CommandMetadata[] $commandMetadata
 * @property string|null $general_response
 */
class Command extends Model
{
    use BroadcastsEvents;

    /** @use HasFactory<CommandFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;

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

    /**
     * Get the parent command
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Command, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the children commands
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Command, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Get the punishes associated with the Command
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Punish, $this>
     */
    public function punishes(): HasMany
    {
        return $this->hasMany(Punish::class);
    }

    /**
     * Get the auto post associated with the Command
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<AutoPost, $this>
     */
    public function autoPost(): BelongsTo
    {
        return $this->belongsTo(AutoPost::class);
    }

    /**
     * Get the command metadata associated with the Command
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CommandMetadata, $this>
     */
    public function commandMetadata(): HasMany
    {
        return $this->hasMany(CommandMetadata::class);
    }

    /**
     * Scope a query to only include active commands
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('enabled', true)->whereDoesntHave('parent', function (Builder $query) {
            return $query->where('enabled', false);
        });
    }

    /**
     * Scope a query to only include regular commands
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeRegular(Builder $query): Builder
    {
        return $query->where('type', 'regular');
    }

    /**
     * Scope a query to only include punishable commands
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopePunishable(Builder $query): Builder
    {
        return $query->where('type', 'punishable');
    }

    /**
     * Scope a query to only include special commands
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeSpecial(Builder $query): Builder
    {
        return $query->where('type', 'special');
    }

    public function getSubscriberCanUseAttribute(): bool
    {
        return $this->usable_by === 'subscribers'
            || $this->usable_by === 'everyone';
    }

    public function getEveryoneCanUseAttribute(): bool
    {
        return $this->usable_by === 'everyone';
    }

    /**
     * @return Attribute<bool, null>
     */
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

    /**
     * @return Attribute<string|null, null>
     */
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

    /**
     * @return Attribute<string|null, null>
     */
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

    /**
     * @return Attribute<int|null, null>
     */
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

    /**
     * @return Attribute<int|null, null>
     */
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

    /**
     * @return Attribute<int|null, null>
     */
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

    /**
     * @return Attribute<string|null, null>
     */
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

    /**
     * @return Attribute<string|null, null>
     */
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

    /**
     * @return Attribute<bool, null>
     */
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

    /**
     * @return Attribute<string|null, null>
     */
    public function generalResponse(): Attribute
    {
        return Attribute::make(
            get: function () {
                $chatMessage = $this->response;

                if ($this->type === 'special') {
                    $commandService = SpecialCommandService::command($this);
                    $chatMessage = $commandService->run();
                }

                return $chatMessage;
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
