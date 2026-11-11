<?php

namespace Database\Factories;

use App\Models\Poster;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Poster>
 */
class PosterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'image_path' => 'affiches/exemple.jpg',
            'caption' => fake()->sentence(6),
        ];
    }
}
