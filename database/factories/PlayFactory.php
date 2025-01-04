<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Play;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Play>
 */
class PlayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dice1' => $this->faker->numberBetween(1, 6),
            'dice2' => $this->faker->numberBetween(1, 6)
        ];
    }
}
