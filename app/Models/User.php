<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Throwable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'avatar',
        'type',
        'twitch_id',
        'twitch_access_token',
        'twitch_refresh_token',
        'twitch_access_token_expires_at',
    ];

    /**
     * The attributes that should be visible for serialization.
     *
     * @var array<int, string>
     */
    protected $visible = [
        'id',
        'username',
        'avatar',
        'type',
        'twitch_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'disabled_at' => 'datetime',
        'twitch_access_token_expires_at' => 'datetime',
    ];

    public function authenticators(): HasMany
    {
        return $this->hasMany(Authenticator::class);
    }

    public function twitchAccessToken(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ((! $this->twitch_access_token_expires_at) || now()->addSeconds(30)->isAfter($this->twitch_access_token_expires_at)) {
                    $value = $this->renewAccessToken();
                }

                return $value;
            },
            set: function ($value) {
                $this->attributes['twitch_access_token'] = $value;
            }
        );
    }

    public function renewAccessToken()
    {
        try {
            $response = Http::post('https://id.twitch.tv/oauth2/token', [
                'client_id' => config('services.twitch.client_id'),
                'client_secret' => config('services.twitch.client_secret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->twitch_refresh_token,
            ]);

            $token = $response->json('access_token');

            $this->twitch_access_token_expires_at = now()->addSeconds($response->json('expires_in'));
            $this->attributes['twitch_access_token'] = $token;
            $this->save();

            return $token;
        } catch (Throwable $th) {
            Log::error($th->getMessage());

            return null;
        }
    }
}
