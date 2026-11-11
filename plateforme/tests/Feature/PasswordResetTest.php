<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_links_to_the_forgotten_password_form(): void
    {
        $this->get(route('login'))->assertSee(route('password.request'));
        $this->get(route('password.request'))->assertOk();
    }

    public function test_a_member_can_choose_a_new_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'awa@example.com']);

        $this->post(route('password.email'), ['email' => 'awa@example.com'])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $mail = $notification->toMail($user);
            $this->assertSame('TEGV · Choisir un nouveau mot de passe', $mail->subject);

            $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))->assertOk();

            $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'victoire2026',
                'password_confirmation' => 'victoire2026',
            ])->assertRedirect(route('login'));

            return true;
        });

        $this->assertTrue(Hash::check('victoire2026', $user->refresh()->password));
    }

    public function test_an_unknown_email_gets_the_same_answer(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'inconnu@example.com'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_a_wrong_token_is_refused(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.update'), [
            'token' => 'faux',
            'email' => $user->email,
            'password' => 'victoire2026',
            'password_confirmation' => 'victoire2026',
        ])->assertSessionHasErrors('email');
    }
}
