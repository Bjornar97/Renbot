<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Authenticator extends Model
{
    protected $fillable = [
        'credential_id',
        'public_key',
    ];

    protected $casts = [
        'public_key' => 'encrypted:json',
    ];

    /**
     * Get the user that owns the Authenticator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
