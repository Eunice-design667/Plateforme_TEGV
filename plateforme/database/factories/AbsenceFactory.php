<?php

namespace Database\Factories;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Absence>
 */
class AbsenceFactory extends Factory
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
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'reason' => fake()->randomElement(['Voyage à Bobo-Dioulasso', 'Mission professionnelle', 'Raisons de santé', 'Formation']),
            'starts_on' => today(),
            'ends_on' => today()->addDays(fake()->numberBetween(3, 20)),
            'is_tegv_christian' => true,
        ];
    }
}
