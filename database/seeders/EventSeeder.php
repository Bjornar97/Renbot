<?php

namespace Database\Seeders;

use App\Models\Creator;
use App\Models\Event;
use App\Models\EventTeam;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::factory()->count(10)->create();

        foreach ($events as $event) {
            $numberOfParticipants = random_int(1, 10);

            $creators = Creator::query()
                ->inRandomOrder()
                ->limit($numberOfParticipants)
                ->get();

            $teams = EventTeam::factory()->count(random_int(0, $numberOfParticipants))->create([
                'event_id' => $event->id,
            ]);

            if (count($teams) > 0) {
                foreach ($creators as $creator) {
                    $event->participants()->save($creator, [
                        'event_team_id' => $teams->random()->id,
                    ]);
                }
            } else {
                $event->participants()->saveMany($creators);
            }
        }
    }
}
