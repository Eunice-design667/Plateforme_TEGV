<?php

namespace Database\Factories;

use App\Enums\ChurchRole;
use App\Enums\LeaderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->randomElement(['Aminata', 'Issa', 'Mariam', 'Paul', 'Salimata', 'Jean', 'Rasmata', 'Jacques', 'Awa', 'Ousmane', 'Esther', 'Daniel', 'Ruth', 'Élie', 'Sarah', 'Moïse']);
        $lastName = fake()->randomElement(['Ouédraogo', 'Sawadogo', 'Compaoré', 'Kaboré', 'Zongo', 'Traoré', 'Kiendrebeogo', 'Ilboudo', 'Nikiéma', 'Zoungrana', 'Bationo', 'Yaméogo']);

        return [
            'name' => "{$firstName} {$lastName}",
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'primary_profile' => $trade = fake()->randomElement(['Électricité', 'Comptabilité', 'Plomberie', 'Droit', 'Soins infirmiers', 'Maçonnerie', 'Informatique', 'Enseignement', 'Mécanique auto', 'Couture', 'Architecture', 'Médecine']),
            'sector' => config('tegv.trade_sectors')[$trade] ?? 'autre',
            'secondary_profiles' => fake()->randomElements(['Formation', 'Conseil', 'Gestion de projet', 'Vente', 'Maintenance', 'Photographie'], fake()->numberBetween(0, 2)),
            'years_experience' => fake()->numberBetween(1, 30),
            'company' => fake()->optional(0.8)->randomElement(['SONABEL', 'ONEA', 'Coris Bank', 'CHU Yalgado', 'Cabinet Kaboré', 'Indépendant', 'Orange Burkina', 'Lycée Philippe Zinda Kaboré']),
            'job_title' => fake()->optional(0.8)->randomElement(['Technicien', 'Chef de service', 'Responsable', 'Gérant', 'Consultant', 'Assistant']),
            'birth_day' => fake()->numberBetween(1, 28),
            'birth_month' => fake()->numberBetween(1, 12),
            'church_roles' => [],
            'other_roles' => [],
            'whatsapp' => '+226 7'.fake()->numerify('# ## ## ##'),
            'contact_email' => fn (array $attributes) => $attributes['email'],
            'is_tegv_christian' => true,
            'profile_completed_at' => now(),
            'leader_status' => LeaderStatus::None,
        ];
    }

    /**
     * An account that has done step 1 (email and password) but not step 2 (profile).
     */
    public function withoutProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'first_name' => null,
            'last_name' => null,
            'primary_profile' => null,
            'birth_day' => null,
            'birth_month' => null,
            'profile_completed_at' => null,
        ]);
    }

    public function pastor(LeaderStatus $status = LeaderStatus::Approved): static
    {
        return $this->state(fn (array $attributes) => [
            'church_roles' => [ChurchRole::Pasteur->value],
            'leader_status' => $status,
            'leader_requested_at' => now()->subDay(),
            'leader_validated_at' => $status === LeaderStatus::Approved ? now() : null,
        ]);
    }

    public function deacon(LeaderStatus $status = LeaderStatus::Approved): static
    {
        return $this->state(fn (array $attributes) => [
            'church_roles' => [ChurchRole::Diacre->value],
            'leader_status' => $status,
            'leader_requested_at' => now()->subDay(),
            'leader_validated_at' => $status === LeaderStatus::Approved ? now() : null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['is_admin' => true]);
    }

    public function bornToday(): static
    {
        return $this->state(fn (array $attributes) => ['birth_day' => today()->day, 'birth_month' => today()->month]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
