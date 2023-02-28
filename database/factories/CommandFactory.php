<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Command>
 */
class CommandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'command' => $this->faker->word(),
            'response' => $this->faker->sentence(),
            'enabled' => $this->faker->boolean(80),
            'cooldown' => 0,
            'global_cooldown' => 0,
            'type' => $this->faker->randomElement(['regular', 'punishable']),
            'usable_by' => $this->faker->randomElement(['moderators', 'subscribers', 'everyone']),
            'severity' => $this->faker->numberBetween(1, 10),
            'punish_reason' => $this->faker->sentence(),
        ];
    }
}
