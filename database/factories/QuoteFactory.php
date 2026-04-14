<?php

namespace Database\Factories;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote' => $this->faker->paragraph(),
            'said_by' => $this->faker->userName(),
            'said_at' => $this->faker->dateTimeBetween('-1 year'),
        ];
    }
}
