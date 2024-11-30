<?php

namespace App\Jobs;

use App\Models\Creator;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mcc = Http::get('https://api.mcchampionship.com/v1/event')->json();
        $mcc = $mcc['data'];

        $existingEvent = Event::query()->where('type', 'mcc')->where('title', "MC Championship - {$mcc['event']}")->delete();

        // if ($existingEvent) {
        //     // TODO: Update everything
        //     $existingEvent->delete();
        // }

        $start = Carbon::parse($mcc['date']);

        $event = Event::query()->create([
            'type' => 'mcc',
            'title' => "MC Championship - {$mcc['event']}",
            'description' => "MC Championship is a Minecraft event that brings together 40 contestants to compete in a series of mini-games that test the core Minecraft skills!",
            'event_url' => 'https://mcc.live',
            'start' => $start,
            'end' => $start->clone()->endOfDay(),
        ]);

        $teams = Http::get('https://api.mcchampionship.com/v1/participants')->json();
        $teams = $teams['data'];

        foreach ($teams as $key => $value) {
            if ($key === 'SPECTATORS' || $key === 'NONE') {
                continue;
            }

            $team = $event->teams()->create([
                'name' => $key,
                'color' => Str::lower($key),
            ]);

            $creators = [];

            foreach ($value as $participant) {
                $creator = Creator::query()->updateOrCreate([
                    'name' => $participant['username'],

                ], [
                    'twitch_url' => $participant['platform'] === 'twitch' ? $participant['stream'] : null,
                    'youtube_url' => $participant['platform'] === 'youtube' || $participant['platform'] === 'other' ? $participant['stream'] : null,
                    'image' => $participant['icon'],
                ]);

                $creators[] = $creator;

                $event->participants()->attach($creator, [
                    'event_team_id' => $team->id,
                ]);
            }
        }
    }
}
