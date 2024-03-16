<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->image) {
                return null;
            }

            return Storage::url($this->image);
        });
    }
}
