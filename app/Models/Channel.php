<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Channel
 *
 * @property string $twitch_channel_id
 * @property string $username
 * @property \Carbon\Carbon|null $live_at
 * @property \Carbon\Carbon|null $offline_at
 * @property-read bool $is_live
 */
class Channel extends Model
{
    protected $fillable = [
        'twitch_channel_id',
        'username',
        'live_at',
        'offline_at',
    ];

    protected $casts = [
        'live_at' => 'datetime',
        'offline_at' => 'datetime',
    ];

    /**
     * Check if the channel is live.
     *
     * @return Attribute<bool, null>
     */
    public function isLive(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                $isLive = $this->live_at !== null;

                $isOffline = $this->offline_at !== null && $this->offline_at->greaterThan($this->live_at);

                return $isLive && ! $isOffline;
            }
        );
    }
}
