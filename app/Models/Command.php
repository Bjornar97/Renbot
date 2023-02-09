<?php

namespace App\Models;

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
}
