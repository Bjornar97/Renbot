<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedTerm extends Model
{
    use HasFactory;

    public $fillable = [
        'twitch_id',
        'comment',
    ];
}
