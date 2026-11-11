<?php

namespace Database\Factories;

use App\Enums\ThanksStatus;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Visitor>
 */
class VisitorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'origin_church' => fake()->randomElement(['Assemblée de Dieu Gounghin', 'Église Baptiste', 'Centre International d’Évangélisation', null]),
            'has_no_church' => fn (array $attributes) => $attributes['origin_church'] === null,
            'whatsapp' => '+226 7'.fake()->numerify('# ## ## ##'),
            'visited_on' => today()->previous(Carbon::SUNDAY),
            'thanks_status' => ThanksStatus::Pending,
        ];
    }
}
