<?php

namespace App\Jobs;

use App\Models\Creator;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GetNextMcc implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    public $teamNameMap = [
        'RED' => 'Red Rabbits',
        'ORANGE' => 'Orange Ocelots',
        'YELLOW' => 'Yellow Yaks',
        'LIME' => 'Lime Llamas',
        'GREEN' => 'Green Geckos',
        'CYAN' => 'Cyan Coyotes',
        'AQUA' => 'Aqua Axolotls',
        'BLUE' => 'Blue Bats',
        'PURPLE' => 'Purple Pandas',
        'PINK' => 'Pink Parrots',
    ];

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mcc = Http::get('https://api.mcchampionship.com/v1/event')->json();
        $mcc = $mcc['data'];

        $dateTime = Carbon::parse($mcc['date']);

        $event = Event::query()
            ->where('type', 'mcc')
            ->whereBetween('start', [$dateTime->subDay(), $dateTime->addDay()])
            ->first();

        $start = Carbon::parse($mcc['date']);

        $title = "MC Championship - {$mcc['event']}";

        if (!$event) {
            $event = Event::query()->create([
                'type' => 'mcc',
                'title' => $title,
                'description' => "MC Championship is a Minecraft event that brings together 40 contestants to compete in a series of mini-games that test the core Minecraft skills!",
                'event_url' => 'https://mcc.live',
                'start' => $start,
                'end' => $start->clone()->endOfDay(),
            ]);
        }

        $teams = Http::get('https://api.mcchampionship.com/v1/participants')->json();
        $teams = $teams['data'];
        $creators = [];

        foreach ($teams as $key => $value) {
            if ($key === 'SPECTATORS' || $key === 'NONE') {
                continue;
            }

            $imageUrl = asset(Storage::url("mcc-teams/" . strtolower($key) . ".webp"));

            $team = $event->teams()->updateOrCreate(
                [
                    'name' => $this->teamNameMap[$key] ?? $key,
                ],
                [
                    'color' => Str::lower($key),
                    'image_url' => $imageUrl,
                ]
            );

            foreach ($value as $participant) {
                $creator = Creator::query()->updateOrCreate([
                    'name' => $participant['username'],

                ], [
                    'twitch_url' => $participant['platform'] === 'twitch' ? $participant['stream'] : null,
                    'youtube_url' => $participant['platform'] === 'youtube' || $participant['platform'] === 'other' ? $participant['stream'] : null,
                    'image' => $participant['icon'],
                ]);

                $creators[$creator->id] = ['event_team_id' => $team->id];
            }
        }

        $event->participants()->sync($creators);
    }
}
