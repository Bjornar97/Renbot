<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Creator extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'youtube_url',
        'twitch_url',
        'x_url',
        'image',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->image) {
                return null;
            }

            if (str_starts_with($this->image, 'https://')) {
                return $this->image;
            }

            return Storage::url($this->image);
        });
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_participants');
    }
}
