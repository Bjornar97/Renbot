<?php

namespace App\Jobs;

use App\Models\Creator;
use App\Models\Event;
use App\Models\EventTeam;
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
        $teams = Http::get('https://api.mcchampionship.com/v1/participants')->json();

        $mcc = (object) $mcc['data'];
        $teams = collect($teams['data']);

        if (! $teams->flatten(1)->contains('username', 'Renthedog')) {
            return;
        }

        $start = Carbon::parse($mcc->date);

        $event = Event::query()
            ->where('type', 'mcc')
            ->whereBetween('start', [$start->subDay(), $start->addDay()])
            ->first();

        $title = "MC Championship - {$mcc->event}";

        if (! $event) {
            $event = Event::query()->create([
                'type' => 'mcc',
                'title' => $title,
                'description' => 'MC Championship is a Minecraft event that brings together 40 contestants to compete in a series of mini-games that test the core Minecraft skills!',
                'event_url' => 'https://mcc.live',
                'start' => $start,
                'end' => $start->clone()->endOfDay(),
            ]);
        }

        $creators = [];

        foreach ($teams as $teamName => $participants) {
            if ($teamName === 'SPECTATORS' || $teamName === 'NONE') {
                continue;
            }

            $imageUrl = asset(Storage::url('mcc-teams/'.strtolower($teamName).'.webp'));

            /** @var EventTeam $team */
            $team = $event->teams()->updateOrCreate(
                [
                    'name' => $this->teamNameMap[$teamName] ?? $teamName,
                ],
                [
                    'color' => Str::lower($teamName),
                    'image_url' => $imageUrl,
                ]
            );

            foreach ($participants as $participant) {
                $data = [
                    'image' => $participant['icon'],
                ];

                // Set twitch_url only if the platform is 'twitch'
                if ($participant['platform'] === 'twitch') {
                    $data['twitch_url'] = $participant['stream'];
                }

                // Set youtube_url only if the platform is 'youtube' or 'other'
                if (in_array($participant['platform'], ['youtube', 'other'])) {
                    $data['youtube_url'] = $participant['stream'];
                }

                $creator = Creator::query()->updateOrCreate(
                    ['name' => $participant['username']],
                    $data
                );

                $creators[$creator->id] = ['event_team_id' => $team->id];
            }
        }

        $event->participants()->sync($creators);
    }
}
