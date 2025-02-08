<?php

namespace App\Models;

use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    use SoftDeletes;

    public $fillable = [
        'quote',
        'said_by',
        'said_at',
    ];

    public $casts = [
        'said_at' => 'date',
    ];
}
