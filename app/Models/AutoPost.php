<?php

namespace App\Models;

use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoPost extends Model
{
    use BroadcastsEvents;

    public $fillable = [
        'title',
        'enabled',
        'interval',
        'interval_type',
        'min_posts_between',
        'last_post',
    ];

    public $casts = [
        'interval' => 'integer',
        'min_posts_between' => 'integer',
        'last_post' => 'datetime',
        'enabled' => 'boolean',
    ];

    protected $appends = [
        'chats_to_next',
    ];

    /**
     * Get the commands associated with the AutoPost.
     *
     * @return HasMany<Command, $this>
     */
    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }

    /**
     * Get the last command that executed the
     *
     * @return BelongsTo<Command, $this>
     */
    public function lastCommand(): BelongsTo
    {
        return $this->belongsTo(Command::class, 'last_command_id');
    }

    /**
     * Get the chats to next attribute.
     *
     * @return Attribute<int, null>
     */
    protected function chatsToNext(): Attribute
    {
        return Attribute::get(function () {
            $messagesSinceLast = Message::query()->where('created_at', '>', $this->last_post ?? now())->count();

            return $this->min_posts_between - $messagesSinceLast;
        });
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @return array<int, Channel|Model>
     */
    public function broadcastOn(string $event): array
    {
        return [$this];
    }
}
