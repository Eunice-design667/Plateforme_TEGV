<?php

namespace Tests\Feature;

use App\Enums\LeaderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_step_one_creates_the_account_and_sends_to_the_profile(): void
    {
        $this->post(route('register'), [
            'email' => 'awa@example.com',
            'password' => 'grace2026',
            'password_confirmation' => 'grace2026',
        ])->assertRedirect(route('profile.create'));

        $this->assertAuthenticated();
        $this->assertFalse(User::sole()->hasCompletedProfile());
    }

    public function test_an_account_without_profile_cannot_open_the_directory(): void
    {
        $user = User::factory()->withoutProfile()->create();

        $this->actingAs($user)->get(route('directory'))->assertRedirect(route('profile.create'));
    }

    public function test_step_two_saves_the_profile(): void
    {
        $user = User::factory()->withoutProfile()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'first_name' => 'Awa',
            'last_name' => 'Ilboudo',
            'primary_profile' => 'Comptabilité', 'sector' => 'autre',
            'secondary_profiles' => ['Fiscalité', 'Audit'],
            'years_experience' => 12,
            'birth_day' => 14,
            'birth_month' => 2,
            'church_roles' => ['chorale', 'autre'],
            'other_roles' => ['Accueil', 'Protocole'],
            'whatsapp' => '+226 70 26 83 95',
            'is_tegv_christian' => '1',
        ])->assertRedirect(route('home'));

        $user->refresh();
        $this->assertTrue($user->hasCompletedProfile());
        $this->assertSame(['Fiscalité', 'Audit'], $user->secondary_profiles);
        $this->assertSame(['Chorale', 'Accueil', 'Protocole'], $user->roleLabels());
        $this->assertSame(LeaderStatus::None, $user->leader_status);
    }

    public function test_declaring_yourself_pastor_puts_the_account_on_hold(): void
    {
        $user = User::factory()->withoutProfile()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'first_name' => 'Daniel',
            'last_name' => 'Nikiéma',
            'primary_profile' => 'Enseignement', 'sector' => 'autre',
            'church_roles' => ['pasteur'],
            'whatsapp' => '70 00 00 00',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame(LeaderStatus::Pending, $user->leader_status);
        $this->assertFalse($user->isLeader());
    }

    public function test_an_impossible_birthday_is_refused(): void
    {
        $user = User::factory()->withoutProfile()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'first_name' => 'Awa', 'last_name' => 'Ilboudo', 'primary_profile' => 'Droit', 'sector' => 'autre',
            'whatsapp' => '70 00 00 00', 'birth_day' => 31, 'birth_month' => 4,
        ])->assertSessionHasErrors('birth_day');
    }
}
