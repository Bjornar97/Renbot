<?php

namespace Database\Factories;

use App\Models\EventTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventTeam>
 */
class EventTeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'color' => $this->faker->colorName(),
        ];
    }
}
