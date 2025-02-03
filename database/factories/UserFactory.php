<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'username' => fake()->name(),
            'type' => fake()->randomElement(['viewer', 'moderator', 'rendogtv']),
            'twitch_id' => fake()->numberBetween(10000, 999999),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user should be moderator type
     *
     * @return static
     */
    public function moderator()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'moderator',
        ]);
    }

    /**
     * Indicate that the user should be rendog type
     *
     * @return static
     */
    public function rendog()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'rendog',
        ]);
    }

    /**
     * Indicate that the user should be viewer type
     *
     * @return static
     */
    public function viewer()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'viewer',
        ]);
    }
}
