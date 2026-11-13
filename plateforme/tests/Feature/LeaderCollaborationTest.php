<?php

namespace Tests\Feature;

use App\Enums\AnnouncementStatus;
use App\Enums\ThanksStatus;
use App\Models\Absence;
use App\Models\Announcement;
use App\Models\Report;
use App\Models\User;
use App\Models\Visitor;
use App\Services\WhatsApp\WhatsAppManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Bilan v8: handled requests are marked so two leaders do not handle them twice.
 */
class LeaderCollaborationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_absence_shows_who_handled_it(): void
    {
        $pastor = User::factory()->pastor()->create(['first_name' => 'Samuel', 'last_name' => 'Ouédraogo']);
        $deacon = User::factory()->deacon()->create();
        $absence = Absence::factory()->create(['first_name' => 'Paul', 'last_name' => 'Zongo']);

        $this->actingAs($pastor)->patch(route('leader.absences.update', $absence))->assertRedirect();
        $this->assertSame($pastor->id, $absence->refresh()->handled_by);

        $this->actingAs($deacon)->get(route('leader.dashboard', ['onglet' => 'absences']))->assertSee('Traitée par Samuel Ouédraogo');

        // A second leader clicking "traiter" does not take it over.
        $this->actingAs($deacon)->patch(route('leader.absences.update', $absence))->assertSessionHas('status', 'Cette absence a déjà été traitée par Samuel Ouédraogo.');
        $this->assertSame($pastor->id, $absence->refresh()->handled_by);

        $this->actingAs($deacon)->patch(route('leader.absences.update', $absence), ['reopen' => '1']);
        $this->assertNull($absence->refresh()->handled_at);

        $this->actingAs(User::factory()->create())->patch(route('leader.absences.update', $absence))->assertForbidden();
    }

    public function test_a_report_shows_who_handled_it(): void
    {
        $deacon = User::factory()->deacon()->create(['first_name' => 'Rasmata', 'last_name' => 'Kaboré']);
        $report = Report::factory()->create();

        $this->actingAs($deacon)->patch(route('leader.reports.update', $report));

        $this->assertSame($deacon->id, $report->refresh()->handled_by);
        $this->actingAs(User::factory()->pastor()->create())->get(route('leader.dashboard', ['onglet' => 'signalements']))->assertSee('Traité par Rasmata Kaboré');
    }

    public function test_information_under_review_is_flagged_and_cannot_be_published_twice(): void
    {
        $first = User::factory()->pastor()->create(['first_name' => 'Samuel', 'last_name' => 'Ouédraogo']);
        $second = User::factory()->deacon()->create();
        $announcement = Announcement::factory()->create(['title' => 'Veillée']);

        $this->actingAs($first)->get(route('leader.announcements.edit', $announcement))->assertOk();
        $this->assertSame($first->id, $announcement->refresh()->claimed_by);

        $this->actingAs($second)->get(route('leader.dashboard'))->assertSee('En relecture par Samuel Ouédraogo');
        $this->actingAs($second)->get(route('leader.announcements.edit', $announcement))->assertSee('En cours de relecture');

        $this->actingAs($first)->put(route('leader.announcements.update', $announcement), ['title' => 'Veillée', 'body' => 'Vendredi']);
        $this->assertSame(AnnouncementStatus::Published, $announcement->refresh()->status);

        $this->actingAs($second)->put(route('leader.announcements.update', $announcement), ['title' => 'Autre titre', 'body' => 'x'])
            ->assertSessionHas('status', 'Cette information a déjà été publiée par Samuel Ouédraogo.');
        $this->assertSame('Veillée', $announcement->refresh()->title);
    }

    public function test_a_review_older_than_30_minutes_no_longer_blocks(): void
    {
        $announcement = Announcement::factory()->create(['claimed_by' => User::factory()->pastor()->create()->id, 'claimed_at' => now()->subMinutes(31)]);

        $this->assertFalse($announcement->isBeingReviewedByAnotherThan(User::factory()->deacon()->create()));
    }

    public function test_an_expired_page_returns_to_the_form_with_a_message(): void
    {
        $this->withMiddleware();
        $this->app['env'] = 'production';

        $this->from(route('visitors.create'))
            ->post(route('visitors.store'), ['first_name' => 'Awa', 'last_name' => 'Ilboudo', '_token' => 'perime'])
            ->assertRedirect(route('visitors.create'))
            ->assertSessionHas('status')
            ->assertSessionHasInput('first_name', 'Awa');
    }

    public function test_visitors_are_thanked_the_day_after_their_visit(): void
    {
        Carbon::setTestNow('2026-11-15 09:00:00'); // Sunday
        $sunday = Visitor::factory()->create(['visited_on' => today()]);
        $this->mock(WhatsAppManager::class, function (MockInterface $mock): void {
            $mock->expects('send')->once()->andReturn('log');
        });

        $this->artisan('tegv:remercier-visiteurs');
        $this->assertSame(ThanksStatus::Pending, $sunday->refresh()->thanks_status);

        Carbon::setTestNow('2026-11-16 10:00:00'); // Monday
        $this->artisan('tegv:remercier-visiteurs');
        $this->assertSame(ThanksStatus::Sent, $sunday->refresh()->thanks_status);
    }

    public function test_the_quit_button_is_gone(): void
    {
        $this->actingAs(User::factory()->create())->get(route('home'))->assertDontSee('Quitter l’application', false)->assertSee('Se déconnecter');
    }
}
