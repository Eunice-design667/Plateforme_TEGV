<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MakeAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_default_administrator(): void
    {
        config(['tegv.admin' => ['email' => 'admin@example.com', 'first_name' => 'Romaric', 'last_name' => 'Sawadogo']]);

        $this->artisan('tegv:admin')
            ->expectsQuestion('Mot de passe (8 caractères minimum, lettres et chiffres)', 'grace2026')
            ->expectsQuestion('Confirmez le mot de passe', 'grace2026')
            ->assertSuccessful();

        $admin = User::sole();
        $this->assertTrue($admin->is_admin);
        $this->assertSame('Romaric Sawadogo', $admin->full_name);
        $this->assertTrue(Hash::check('grace2026', $admin->password));
        $this->assertFalse($admin->hasCompletedProfile());
    }

    public function test_a_weak_password_is_refused(): void
    {
        $this->artisan('tegv:admin', ['email' => 'admin@example.com', '--prenom' => 'Awa', '--nom' => 'Ilboudo'])
            ->expectsQuestion('Mot de passe (8 caractères minimum, lettres et chiffres)', 'court')
            ->expectsQuestion('Confirmez le mot de passe', 'court')
            ->assertFailed();

        $this->assertDatabaseEmpty('users');
    }

    public function test_it_promotes_an_existing_account(): void
    {
        $member = User::factory()->create();

        $this->artisan('tegv:admin', ['email' => $member->email])->assertSuccessful();

        $this->assertTrue($member->refresh()->is_admin);
    }
}
