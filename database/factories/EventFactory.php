<?php

namespace Database\Factories;

use App\Enums\EventType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', '+1 year');
        $end = $this->faker->dateTimeBetween($start, Carbon::parse($start)->addHours(6));

        $type = $this->faker->randomElement(EventType::cases());

        return [
            'type' => $type,
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(3, true),
            'event_url' => $type === EventType::MCC ? 'https://mcc.live' : null,
            'start' => $start,
            'end' => $end,
        ];
    }
}
