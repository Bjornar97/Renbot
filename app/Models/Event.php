<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;

    public $fillable = [
        'type',
        'title',
        'description',
        'event_url',
        'start',
        'end',
    ];

    protected $casts = [
        'start' => 'datetime:Y-m-d\TH:i:s.uP',
        'end' => 'datetime:Y-m-d\TH:i:s.uP',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            $event->slug = self::generateUniqueSlug($event->title);
        });
    }

    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Creator::class, 'event_participants')
            ->orderByRaw("CASE WHEN name = 'Renthedog' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->withPivot('event_team_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(EventTeam::class)
            ->orderByRaw("
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM event_participants 
                    JOIN creators ON creators.id = event_participants.creator_id 
                    WHERE event_participants.event_team_id = event_teams.id 
                    AND creators.name = 'Renthedog'
                ) THEN 0 
                ELSE 1 
            END
        ")
            ->orderBy('name');
    }

    public function scopeUpcoming(Builder $query)
    {
        return $query->where('end', '>', now()->subDay());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }
}
