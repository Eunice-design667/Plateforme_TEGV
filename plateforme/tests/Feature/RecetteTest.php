<?php

namespace Tests\Feature;

use App\Enums\AnnouncementStatus;
use App\Enums\LeaderStatus;
use App\Enums\ReportType;
use App\Enums\ThanksStatus;
use App\Models\Absence;
use App\Models\Announcement;
use App\Models\BirthdayMessage;
use App\Models\Report;
use App\Models\User;
use App\Models\Visitor;
use App\Services\MessageWriter;
use App\Services\WhatsApp\WhatsAppManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Automated checks for the "Cahier de recette" (test plan). Each test name carries the plan reference.
 */
class RecetteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function profile(array $overrides = []): array
    {
        return [
            'first_name' => 'Awa', 'last_name' => 'Ilboudo', 'primary_profile' => 'Comptabilité', 'sector' => 'autre',
            'whatsapp' => '70 26 83 95', ...$overrides,
        ];
    }

    public function test_f0_01_02_03_first_visit_screen(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('/images/logo-rond.png')
            ->assertSeeInOrder(['Bienvenue au', 'TEGV'])
            ->assertSee('Trouver un frère ou une sœur')
            ->assertSee('Prévenir les responsables')
            ->assertSee('Suivre la vie de l’église')
            ->assertSee('Créer mon compte')
            ->assertSee('J’ai déjà un compte')
            ->assertSee('Scannez le QR code affiché');
    }

    public function test_f0_06_bad_accounts_are_refused(): void
    {
        User::factory()->create(['email' => 'pris@example.com']);

        $this->post(route('register'), ['email' => 'a@example.com', 'password' => 'grace2026', 'password_confirmation' => 'autre2026'])->assertSessionHasErrors('password');
        $this->post(route('register'), ['email' => 'pas-un-email', 'password' => 'grace2026', 'password_confirmation' => 'grace2026'])->assertSessionHasErrors('email');
        $this->post(route('register'), ['email' => 'pris@example.com', 'password' => 'grace2026', 'password_confirmation' => 'grace2026'])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_f0_07_without_profile_the_member_is_sent_back_to_the_profile(): void
    {
        $account = User::factory()->withoutProfile()->create();

        $this->post(route('login'), ['email' => $account->email, 'password' => 'password'])->assertRedirect(route('home'));
        $this->get(route('home'))->assertRedirect(route('profile.create'));
        $this->get(route('directory'))->assertRedirect(route('profile.create'));
    }

    public function test_f0_08_back_from_step_two_keeps_the_account(): void
    {
        $account = User::factory()->withoutProfile()->create(['email' => 'awa@example.com']);

        $this->actingAs($account)->get(route('profile.create'))->assertSee(route('register'));
        $this->actingAs($account)->get(route('register'))->assertOk()->assertSee('awa@example.com')->assertSee('Votre compte est déjà créé');

        $this->actingAs($account)->post(route('register'), ['email' => 'awa.ilboudo@example.com'])->assertRedirect(route('profile.create'));

        $this->assertSame('awa.ilboudo@example.com', $account->refresh()->email);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_f0_09_wrong_password_is_refused(): void
    {
        $member = User::factory()->create();

        $this->post(route('login'), ['email' => $member->email, 'password' => 'mauvais'])->assertSessionHasErrors(['email' => 'Email ou mot de passe incorrect.']);
        $this->assertGuest();
    }

    public function test_f1_02_required_fields_are_flagged(): void
    {
        $this->actingAs(User::factory()->withoutProfile()->create())
            ->put(route('profile.update'), [])
            ->assertSessionHasErrors(['first_name', 'last_name', 'primary_profile', 'whatsapp']);
    }

    public function test_f1_03_and_s_07_photo_checks(): void
    {
        Storage::fake('media');
        $member = User::factory()->create();

        $this->actingAs($member)->put(route('profile.update'), $this->profile(['photo' => UploadedFile::fake()->image('moi.jpg')]))->assertSessionHasNoErrors();
        $this->assertNotNull($member->refresh()->photo_path);

        $this->actingAs($member)->put(route('profile.update'), $this->profile(['photo' => UploadedFile::fake()->image('lourde.jpg')->size(5000)]))->assertSessionHasErrors('photo');
        $this->actingAs($member)->put(route('profile.update'), $this->profile(['photo' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')]))->assertSessionHasErrors('photo');
    }

    public function test_f1_05_to_f1_09_roles(): void
    {
        $member = User::factory()->withoutProfile()->create();

        $this->actingAs($member)->get(route('profile.create'))
            ->assertSeeInOrder(['Pasteur', 'Diacre', 'Ancien', 'Équipe de louange', 'Chorale', 'Autre'])
            ->assertDontSee('Fidèle')
            ->assertSee('Ajouter');

        $this->actingAs($member)->put(route('profile.update'), $this->profile([
            'church_roles' => ['ancien', 'chorale', 'autre'],
            'other_roles' => ['Accueil', 'Protocole', 'accueil'],
        ]))->assertSessionHasNoErrors();
        $this->assertSame(['Ancien', 'Chorale', 'Accueil', 'Protocole'], $member->refresh()->roleLabels());

        // F1-08: a removed tag is simply not sent anymore.
        $this->actingAs($member)->put(route('profile.update'), $this->profile(['church_roles' => ['autre'], 'other_roles' => ['Protocole']]));
        $this->assertSame(['Protocole'], $member->refresh()->roleLabels());

        // F1-09: no role at all is fine.
        $this->actingAs($member)->put(route('profile.update'), $this->profile())->assertSessionHasNoErrors();
        $this->assertSame([], $member->refresh()->roleLabels());
    }

    public function test_f1_04_and_f1_16_trades_and_member_sheet(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->put(route('profile.update'), $this->profile([
            'primary_profile' => 'Plomberie', 'sector' => 'autre', 'secondary_profiles' => ['Soudure', 'Peinture'],
            'company' => 'ONEA', 'job_title' => 'Chef d’équipe', 'years_experience' => 12, 'contact_email' => 'awa@example.com',
        ]));

        $this->actingAs(User::factory()->create())->get(route('members.show', $member))
            ->assertSee('Plomberie')->assertSee('Soudure')->assertSee('Peinture')
            ->assertSee('ONEA')->assertSee('Chef d’équipe', false)->assertSee('12')
            ->assertSee('https://wa.me/22670268395')
            ->assertSee('mailto:awa@example.com');
    }

    public function test_f1_10_tegv_christian_can_be_ticked_and_unticked(): void
    {
        $member = User::factory()->create(['is_tegv_christian' => false]);

        $this->actingAs($member)->put(route('profile.update'), $this->profile(['is_tegv_christian' => '1']));
        $this->assertTrue($member->refresh()->is_tegv_christian);

        $this->actingAs($member)->put(route('profile.update'), $this->profile());
        $this->assertFalse($member->refresh()->is_tegv_christian);
    }

    public function test_f1_12_search_by_trade_company_skill_role_and_name(): void
    {
        $awa = User::factory()->create([
            'first_name' => 'Awa', 'last_name' => 'Ilboudo', 'primary_profile' => 'Comptabilité', 'sector' => 'autre', 'company' => 'Coris Bank',
            'secondary_profiles' => ['Fiscalité'], 'church_roles' => ['chorale'],
        ]);
        User::factory()->create(['first_name' => 'Issa', 'last_name' => 'Zongo', 'primary_profile' => 'Maçonnerie', 'sector' => 'autre', 'company' => 'Indépendant', 'secondary_profiles' => [], 'church_roles' => [], 'job_title' => 'Gérant']);

        foreach (['comptabilite', 'Coris', 'fiscalité', 'chorale', 'ilboudo'] as $term) {
            $this->actingAs($awa)->get(route('directory', ['q' => $term]))->assertSee('Awa Ilboudo')->assertDontSee('Issa Zongo');
        }
    }

    public function test_f1_14_ranking_by_experience(): void
    {
        foreach ([2, 15, 8] as $years) {
            User::factory()->create(['first_name' => "Exp{$years}", 'last_name' => 'Test', 'primary_profile' => 'Menuiserie', 'sector' => 'autre', 'years_experience' => $years]);
        }

        $this->actingAs(User::factory()->create())->get(route('directory', ['q' => 'menuiserie']))
            ->assertSeeInOrder(['Exp15 Test', 'Exp8 Test', 'Exp2 Test'])
            ->assertSee('15 ans d’expérience', false);
    }

    public function test_f1_20_no_result_message(): void
    {
        $this->actingAs(User::factory()->create())->get(route('directory', ['q' => 'zzzz']))->assertOk()->assertSee('Aucun membre trouvé');
    }

    public function test_f2_02_06_visitor_form_fields_and_invalid_number(): void
    {
        $this->get(route('visitors.create'))
            ->assertSee('name="first_name"', false)->assertSee('name="last_name"', false)
            ->assertSee('name="origin_church"', false)->assertSee('Je n’ai pas d’église')
            ->assertSee('name="whatsapp"', false)->assertSee('message de remerciement du TEGV sur WhatsApp')
            ->assertDontSee('type="date"', false)
            ->assertSee('/images/logo-rond.png');

        foreach (['123', 'abc', '70 26'] as $number) {
            $this->post(route('visitors.store'), [
                'first_name' => 'Awa', 'last_name' => 'Ilboudo', 'has_no_church' => '1', 'whatsapp' => $number, 'consent' => '1',
            ])->assertSessionHasErrors('whatsapp');
        }

        $this->assertDatabaseEmpty('visitors');
    }

    public function test_f2_03_04_05_visitor_appears_in_the_list_with_the_sunday_date(): void
    {
        Carbon::setTestNow('2026-11-08 09:00:00');

        $this->post(route('visitors.store'), [
            'first_name' => 'Awa', 'last_name' => 'Ilboudo', 'has_no_church' => '1', 'whatsapp' => '+226 70 26 83 95', 'consent' => '1',
        ])->assertRedirect(route('visitors.thanks'));

        $this->followRedirects($this->get(route('visitors.thanks')))->assertSee('Merci');
        $this->actingAs(User::factory()->deacon()->create())->get(route('leader.dashboard', ['onglet' => 'visiteurs']))
            ->assertSee('Awa Ilboudo')->assertSee('Pas d’église', false)->assertSee('dim. 8 nov.');
    }

    public function test_f2_09_f3_04_f4_03_only_approved_leaders_see_private_lists(): void
    {
        Report::factory()->create(['concerned_person' => 'Frère Issa']);
        Absence::factory()->create(['reason' => 'Voyage à Bobo']);
        Visitor::factory()->create(['first_name' => 'Visiteuse', 'last_name' => 'Test']);

        $allowed = [User::factory()->pastor()->create(), User::factory()->deacon()->create()];
        $refused = [User::factory()->create(), User::factory()->admin()->create(), User::factory()->pastor(LeaderStatus::Pending)->create()];

        foreach ($allowed as $leader) {
            $this->actingAs($leader)->get(route('leader.dashboard', ['onglet' => 'signalements']))->assertSee('Frère Issa');
            $this->actingAs($leader)->get(route('leader.dashboard', ['onglet' => 'absences']))->assertSee('Voyage à Bobo');
            $this->actingAs($leader)->get(route('leader.dashboard', ['onglet' => 'visiteurs']))->assertSee('Visiteuse Test');
        }

        foreach ($refused as $user) {
            foreach (['signalements', 'absences', 'visiteurs'] as $tab) {
                $this->actingAs($user)->get(route('leader.dashboard', ['onglet' => $tab]))->assertForbidden();
            }
        }
    }

    public function test_f2_13_and_s_02_guests_only_reach_the_visitor_form(): void
    {
        foreach (['directory', 'leader.dashboard', 'admin.qr-code', 'admin.rights.index', 'home', 'notices.create'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }

        $this->get(route('visitors.create'))->assertOk();
    }

    public function test_f3_02_03_absences(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->post(route('absences.store'), [
            'first_name' => 'Paul', 'last_name' => 'Zongo', 'reason' => 'Voyage', 'starts_on' => '2026-11-20', 'ends_on' => '2026-11-10',
        ])->assertSessionHasErrors('ends_on', null, 'absence');

        $this->actingAs($member)->post(route('absences.store'), [
            'first_name' => 'Paul', 'last_name' => 'Zongo', 'reason' => 'Voyage', 'starts_on' => today()->toDateString(), 'ends_on' => today()->addDays(5)->toDateString(), 'is_tegv_christian' => '1',
        ])->assertRedirect(route('home'))->assertSessionHas('status');

        $this->actingAs(User::factory()->deacon()->create())->get(route('leader.dashboard', ['onglet' => 'absences']))->assertSee('Paul Zongo');
    }

    public function test_f4_02_05_reports_keep_type_author_date_and_do_not_leak(): void
    {
        $author = User::factory()->create(['first_name' => 'Paul', 'last_name' => 'Zongo']);

        foreach ([ReportType::Deces, ReportType::Autre] as $type) {
            $this->actingAs($author)->post(route('reports.store'), [
                'type' => $type->value, 'concerned_person' => "Personne {$type->value}", 'description' => 'Détails confidentiels',
            ])->assertRedirect(route('home'));
        }

        $this->actingAs(User::factory()->pastor()->create())->get(route('leader.dashboard', ['onglet' => 'signalements']))
            ->assertSee('Décès')->assertSee('Autre problème')->assertSee('Signalé par Paul Zongo');

        $member = User::factory()->create();
        foreach (['home', 'directory', 'announcements.index'] as $route) {
            $this->actingAs($member)->get(route($route))->assertDontSee('Détails confidentiels')->assertDontSee('Personne deces');
        }
    }

    public function test_f5_01_to_f5_07_information_lifecycle(): void
    {
        $author = User::factory()->create();
        $christian = User::factory()->create(['is_tegv_christian' => true]);
        $outsider = User::factory()->create(['is_tegv_christian' => false]);
        $pastor = User::factory()->pastor()->create(['first_name' => 'Samuel', 'last_name' => 'Ouédraogo']);

        $this->actingAs($author)->post(route('announcements.store'), ['title' => 'Veillée', 'body' => 'Vendredi 21h']);
        $announcement = Announcement::sole();
        $this->assertSame(AnnouncementStatus::Proposed, $announcement->status);

        // F5-06: a member cannot publish.
        $this->actingAs($author)->put(route('leader.announcements.update', $announcement), ['title' => 'x', 'body' => 'y'])->assertForbidden();
        // F5-07: proposed, only author and leaders see it.
        $this->actingAs($author)->get(route('announcements.create'))->assertSee('Veillée')->assertSee('Proposée');
        $this->actingAs($christian)->get(route('home'))->assertDontSee('Veillée');
        $this->actingAs($pastor)->get(route('leader.dashboard'))->assertSee('Veillée');

        // F5-03: edit then publish, with publisher and date.
        $this->actingAs($pastor)->put(route('leader.announcements.update', $announcement), ['title' => 'Veillée de prière', 'body' => 'Vendredi 21h au temple']);
        $this->actingAs($christian)->get(route('home'))
            ->assertSee('Veillée de prière')->assertSee('Publiée le')->assertSee('Samuel Ouédraogo (pasteur)');

        // F5-04: also in the christian's own profile.
        $this->actingAs($christian)->get(route('members.show', $christian))->assertSee('Informations de l’église', false)->assertSee('Veillée de prière');

        // F5-05: not for members who did not tick "chrétien de TEGV".
        $this->actingAs($outsider)->get(route('home'))->assertDontSee('Veillée de prière');
        $this->actingAs($outsider)->get(route('members.show', $outsider))->assertDontSee('Veillée de prière');
    }

    public function test_f6_01_02_birthday_field(): void
    {
        $this->actingAs(User::factory()->withoutProfile()->create())->get(route('profile.create'))
            ->assertSee('name="birth_day"', false)->assertSee('name="birth_month"', false)
            ->assertDontSee('birth_year')
            ->assertSee('uniquement pour connaître le jour de votre anniversaire');
    }

    public function test_f6_03_04_05_birthdays_of_the_day(): void
    {
        Carbon::setTestNow('2026-11-11 09:00:00');
        foreach (['Paul', 'Jean', 'Jacques'] as $name) {
            User::factory()->create(['first_name' => $name, 'birth_day' => 11, 'birth_month' => 11]);
        }
        $viewer = User::factory()->create(['birth_day' => 3, 'birth_month' => 5]);

        $this->actingAs($viewer)->get(route('home'))->assertSee('Jacques, Jean et Paul');
        $this->actingAs(User::factory()->create(['birth_day' => 1, 'birth_month' => 1]))->get(route('members.show', $viewer))->assertDontSee('3 mai')->assertDontSee('03/05');
        $this->actingAs($viewer)->get(route('directory'))->assertDontSee('3 mai');

        Carbon::setTestNow('2026-11-12 09:00:00');
        $this->actingAs($viewer)->get(route('home'))->assertSee('Pas d’anniversaire aujourd’hui', false);
    }

    public function test_f6_06_day_changes_at_midnight_ouagadougou(): void
    {
        $this->assertSame('Africa/Ouagadougou', config('app.timezone'));
    }

    public function test_f6_07_29_february(): void
    {
        $leapling = User::factory()->create(['first_name' => 'Bissextile', 'birth_day' => 29, 'birth_month' => 2]);
        $viewer = User::factory()->create(['birth_day' => 1, 'birth_month' => 1]);

        Carbon::setTestNow('2027-02-28 09:00:00');
        $this->actingAs($viewer)->get(route('home'))->assertSee('Bissextile');

        Carbon::setTestNow('2028-02-28 09:00:00');
        $this->actingAs($viewer)->get(route('home'))->assertDontSee('Bissextile');
        Carbon::setTestNow('2028-02-29 09:00:00');
        $this->actingAs($viewer)->get(route('home'))->assertSee('Bissextile');

        // A 30-day month refuses 31.
        $this->actingAs($leapling)->put(route('profile.update'), $this->profile(['birth_day' => 31, 'birth_month' => 6]))->assertSessionHasErrors('birth_day');
    }

    public function test_f6_09_10_11_one_birthday_message_on_the_platform_only(): void
    {
        Carbon::setTestNow('2026-11-11 08:00:00');
        $member = User::factory()->create(['first_name' => 'Esther', 'birth_day' => 11, 'birth_month' => 11]);
        $this->mock(WhatsAppManager::class, fn (MockInterface $mock) => $mock->shouldNotReceive('send'));

        $this->actingAs($member)->get(route('home'))->assertSee('Joyeux anniversaire Esther');
        $this->actingAs($member)->get(route('home'));
        $this->artisan('tegv:anniversaires');

        $this->assertSame(1, BirthdayMessage::count());
    }

    public function test_d_01_to_d_08_rights(): void
    {
        $candidate = User::factory()->withoutProfile()->create();
        $this->actingAs($candidate)->put(route('profile.update'), $this->profile(['church_roles' => ['pasteur']]));
        $candidate->refresh();
        $this->assertSame(LeaderStatus::Pending, $candidate->leader_status);
        $this->actingAs($candidate)->get(route('leader.dashboard'))->assertForbidden();

        $admin = User::factory()->admin()->create(['first_name' => 'Romaric', 'last_name' => 'Sawadogo']);
        $this->actingAs($admin)->get(route('admin.rights.index'))->assertSee($candidate->full_name)->assertSee('Demande du');

        // D-07: neither a member nor a deacon can approve.
        $this->actingAs(User::factory()->deacon()->create())->patch(route('admin.rights.update', $candidate))->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.rights.update', $candidate));
        $candidate->refresh();
        $this->assertSame(LeaderStatus::Approved, $candidate->leader_status);
        $this->assertSame($admin->id, $candidate->leader_validated_by);
        $this->assertNotNull($candidate->leader_validated_at);
        $this->actingAs($candidate)->get(route('leader.dashboard'))->assertOk();

        // D-05: removal is immediate.
        $this->actingAs($admin)->delete(route('admin.rights.destroy', $candidate));
        $this->actingAs($candidate->refresh())->get(route('leader.dashboard'))->assertForbidden();

        // D-08: a member who later declares himself pastor gets nothing before approval.
        $member = User::factory()->create();
        $this->actingAs($member)->put(route('profile.update'), $this->profile(['church_roles' => ['pasteur']]));
        $this->assertFalse($member->refresh()->isLeader());
    }

    public function test_ag_02_standard_thanks_are_varied_and_personal(): void
    {
        $writer = app(MessageWriter::class);
        $messages = collect(['Awa', 'Issa', 'Mariam', 'Paul', 'Rasmata', 'Jean'])->map(fn (string $name) => $writer->templateThanks($name));

        $messages->each(fn (string $message, int $i) => $this->assertStringContainsString(['Awa', 'Issa', 'Mariam', 'Paul', 'Rasmata', 'Jean'][$i], $message));
        $this->assertGreaterThan(1, $messages->map(fn (string $message) => preg_replace('/^\S+\s+\S+/', '', $message))->unique()->count());
    }

    public function test_ag_03_sent_date_is_shown(): void
    {
        Visitor::factory()->create(['first_name' => 'Awa', 'thanks_status' => ThanksStatus::Sent, 'thanks_sent_at' => Carbon::parse('2026-11-09 10:00')]);

        $this->actingAs(User::factory()->deacon()->create())->get(route('leader.dashboard', ['onglet' => 'visiteurs']))
            ->assertSee('Envoyé')->assertSee('le 9 nov. à 10h00');
    }

    public function test_ag_10_and_s_09_a_visitor_can_be_deleted_on_request(): void
    {
        $visitor = Visitor::factory()->create();

        $this->actingAs(User::factory()->create())->delete(route('leader.visitors.destroy', $visitor))->assertForbidden();
        $this->actingAs(User::factory()->deacon()->create())->delete(route('leader.visitors.destroy', $visitor))->assertRedirect();

        $this->assertModelMissing($visitor);
        $this->mock(WhatsAppManager::class, fn (MockInterface $mock) => $mock->shouldNotReceive('send'));
        $this->artisan('tegv:remercier-visiteurs')->expectsOutput('Aucun visiteur à remercier.');
    }

    public function test_s_05_password_strength_and_login_throttling(): void
    {
        $this->post(route('register'), ['email' => 'a@example.com', 'password' => 'court', 'password_confirmation' => 'court'])->assertSessionHasErrors('password');
        $this->post(route('register'), ['email' => 'a@example.com', 'password' => 'motdepasse', 'password_confirmation' => 'motdepasse'])->assertSessionHasErrors('password');

        $member = User::factory()->create();
        foreach (range(1, 5) as $attempt) {
            $this->post(route('login'), ['email' => $member->email, 'password' => 'mauvais']);
        }
        $this->post(route('login'), ['email' => $member->email, 'password' => 'password'])->assertTooManyRequests();
    }

    public function test_s_06_private_pages_are_not_cached(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('directory'));

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_s_08_special_text_is_escaped(): void
    {
        $member = User::factory()->create();
        $this->actingAs($member)->put(route('profile.update'), $this->profile(['company' => '<script>alert("x")</script> « Société » & Cie']));

        $this->actingAs(User::factory()->create())->get(route('members.show', $member))
            ->assertDontSee('<script>alert("x")</script>', false)
            ->assertSee('&lt;script&gt;', false);

        $this->actingAs($member)->put(route('profile.update'), $this->profile(['company' => str_repeat('A', 500)]))->assertSessionHasErrors('company');
    }

    public function test_s_11_no_secret_reaches_the_browser(): void
    {
        config(['services.anthropic.key' => 'sk-ant-secret', 'services.whatsapp.gateway.token' => 'gateway-secret', 'tegv.facebook.token' => 'fb-secret']);
        $leader = User::factory()->admin()->pastor()->create();

        foreach (['home', 'directory', 'leader.dashboard', 'admin.qr-code'] as $route) {
            $this->actingAs($leader)->get(route($route))->assertDontSee('sk-ant-secret')->assertDontSee('gateway-secret')->assertDontSee('fb-secret');
        }

        foreach (glob(public_path('build/assets/*')) as $asset) {
            $this->assertStringNotContainsString('ANTHROPIC', (string) file_get_contents($asset));
        }
    }

    public function test_p_02_directory_with_300_members_stays_fast(): void
    {
        User::factory()->count(300)->create();
        $viewer = User::factory()->create();

        $start = microtime(true);
        $this->actingAs($viewer)->get(route('directory', ['q' => 'informatique']))->assertOk();
        $this->actingAs($viewer)->get(route('directory'))->assertOk();

        $this->assertLessThan(3, microtime(true) - $start);
    }

    public function test_p_05_forms_keep_drafts_and_an_offline_page_exists(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('notices.create'))->assertSee('data-draft="absence-'.$member->id.'"', false)->assertSee('data-draft="signalement-'.$member->id.'"', false);
        $this->actingAs($member)->get(route('profile.edit'))->assertSee('data-draft="profil-'.$member->id.'"', false);
        $this->actingAs($member)->get(route('announcements.create'))->assertSee('data-draft="information-'.$member->id.'"', false);
        $this->get(route('visitors.create'))->assertSee('data-draft="visiteur"', false);

        $this->assertFileExists(public_path('hors-ligne.html'));
        $this->assertStringContainsString('hors-ligne.html', (string) file_get_contents(public_path('sw.js')));
    }

    public function test_p_06_monday_task_is_scheduled(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('tegv:remercier-visiteurs')->assertSuccessful();
    }
}
