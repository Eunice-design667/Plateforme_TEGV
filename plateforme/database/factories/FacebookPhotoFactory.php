<?php

namespace Database\Factories;

use App\Models\FacebookPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacebookPhoto>
 */
class FacebookPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facebook_id' => (string) fake()->unique()->numerify('1############'),
            'image_path' => 'facebook/exemple.jpg',
            'caption' => fake()->sentence(),
            'width' => 960,
            'height' => 720,
            'posted_at' => now(),
        ];
    }
}
