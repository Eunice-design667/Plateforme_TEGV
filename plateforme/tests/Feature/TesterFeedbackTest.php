<?php

namespace Tests\Feature;

use App\Enums\LeaderStatus;
use App\Models\Poster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feedback from the first test campaign (Bilan d'avancement v2).
 */
class TesterFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_f1_03_a_10_mb_profile_photo_is_refused_with_a_clear_limit(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->put(route('profile.update'), [
            'first_name' => 'Awa', 'last_name' => 'Ilboudo', 'primary_profile' => 'Droit', 'sector' => 'autre', 'whatsapp' => '70 26 83 95',
            'photo' => UploadedFile::fake()->image('grande.jpg')->size(10 * 1024),
        ])->assertSessionHasErrors('photo');

        $this->actingAs($member)->get(route('profile.edit'))->assertSee('data-max-mb="3"', false)->assertSee('3 Mo maximum', false);
    }

    public function test_logout_is_reachable_on_phone(): void
    {
        $this->actingAs(User::factory()->create())->get(route('home'))
            ->assertSee('Menu du compte')
            ->assertSee('Se déconnecter');

        $this->actingAs(User::factory()->create())->get(route('profile.edit'))->assertSee('Se déconnecter')->assertSee('Supprimer mon compte');
    }

    public function test_account_deletion_hides_the_member_and_can_be_cancelled(): void
    {
        $member = User::factory()->create(['first_name' => 'Awa', 'last_name' => 'Ilboudo']);
        $viewer = User::factory()->create();

        $this->actingAs($member)->get(route('account-deletion.create'))->assertOk()->assertSee('Pendant 30 jours');
        $this->actingAs($member)->post(route('account-deletion.store'), ['password' => 'mauvais', 'confirmation' => '1'])
            ->assertSessionHasErrors('password', null, 'deletion');

        $this->actingAs($member)->post(route('account-deletion.store'), ['password' => 'password', 'confirmation' => '1'])
            ->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertNotNull($member->refresh()->deletion_requested_at);

        $this->actingAs($viewer)->get(route('directory'))->assertDontSee('Awa Ilboudo');
        $this->actingAs($viewer)->get(route('members.show', $member))->assertNotFound();
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.rights.index'))->assertSee('Awa Ilboudo')->assertSee('suppression définitive le');

        $this->actingAs($member)->get(route('home'))->assertSee('Annuler la suppression');
        $this->actingAs($member)->delete(route('account-deletion.destroy'))->assertRedirect(route('home'));
        $this->assertNull($member->refresh()->deletion_requested_at);
        $this->actingAs($viewer)->get(route('directory'))->assertSee('Awa Ilboudo');
    }

    public function test_accounts_are_deleted_for_good_after_30_days(): void
    {
        Storage::fake('media');
        Storage::disk('media')->put('photos/awa.jpg', 'x');
        $leaving = User::factory()->create(['photo_path' => 'photos/awa.jpg', 'deletion_requested_at' => now()->subDays(30)->subMinute()]);
        Poster::factory()->for($leaving)->create(['image_path' => 'affiches/a.jpg']);
        $recent = User::factory()->create(['deletion_requested_at' => now()->subDays(29)]);

        $this->artisan('tegv:supprimer-comptes')->assertSuccessful();

        $this->assertModelMissing($leaving);
        $this->assertModelExists($recent);
        $this->assertDatabaseCount('posters', 0);
        Storage::disk('media')->assertMissing('photos/awa.jpg');
    }

    public function test_daily_tasks_include_account_deletion(): void
    {
        config(['tegv.cron_secret' => 'secret']);
        Carbon::setTestNow('2026-11-18 10:00:00');

        $this->get(route('daily-tasks'), ['Authorization' => 'Bearer secret'])->assertJsonStructure(['tegv:supprimer-comptes']);
    }

    public function test_stay_logged_in_is_an_explicit_choice(): void
    {
        $this->get(route('login'))->assertSee('Rester connecté')->assertDontSee('name="remember" value="1" checked', false);
        $member = User::factory()->create();

        $this->post(route('login'), ['email' => $member->email, 'password' => 'password'])->assertSessionHas('ephemeral', true);
        $this->get(route('home'))->assertSee('data-ephemeral', false);
        $this->post(route('logout'));

        $this->post(route('login'), ['email' => $member->email, 'password' => 'password', 'remember' => '1'])->assertSessionHas('ephemeral', false);
        $this->get(route('home'))->assertDontSee('data-ephemeral', false);

        $this->assertSame('/', json_decode((string) file_get_contents(public_path('manifest.webmanifest')), true)['start_url']);
    }

    public function test_main_navigation_links_are_marked_for_history_replacement(): void
    {
        $this->actingAs(User::factory()->create())->get(route('home'))->assertSee('data-nav', false);
    }

    public function test_tester_accounts_are_created_per_profile_with_working_rights(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'testeurs');

        $this->artisan('tegv:comptes-testeurs', ['fichier' => $file])->assertSuccessful();
        $rows = array_map('str_getcsv', array_slice(file($file, FILE_IGNORE_NEW_LINES), 1));
        unlink($file);

        $this->assertCount(7, $rows);
        $accounts = collect($rows)->mapWithKeys(fn (array $row) => [$row[1] => $row[2]]);

        foreach ($accounts as $email => $password) {
            $this->assertTrue(Hash::check($password, User::where('email', $email)->value('password')), $email);
        }

        $by = fn (string $key) => User::where('email', "testeur.{$key}@tegv.test")->first();
        $this->assertTrue($by('pasteur')->isLeader());
        $this->assertTrue($by('diacre')->isLeader());
        $this->assertFalse($by('ancien')->isLeader());
        $this->assertTrue($by('admin')->is_admin);
        $this->assertFalse($by('admin')->isLeader());
        $this->assertSame(LeaderStatus::Pending, $by('attente')->leader_status);
        $this->assertFalse($by('externe')->canSeeAnnouncements());

        $this->post(route('login'), ['email' => 'testeur.diacre@tegv.test', 'password' => $accounts['testeur.diacre@tegv.test']]);
        $this->get(route('leader.dashboard'))->assertOk();
    }
}
