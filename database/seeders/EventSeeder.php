<?php

namespace Database\Seeders;

use App\Models\Creator;
use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::factory()->count(50)->create();

        foreach ($events as $event) {
            $event->participants()
                ->saveMany(
                    Creator::query()
                        ->inRandomOrder()
                        ->limit(random_int(1, 10))
                        ->get()
                );
        }
    }
}
