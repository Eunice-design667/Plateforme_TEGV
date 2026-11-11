<?php

namespace Tests\Feature;

use App\Enums\LeaderStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('directory'))->assertRedirect(route('login'));
        $this->get(route('leader.dashboard'))->assertRedirect(route('login'));
    }

    public function test_a_member_cannot_see_reports_or_visitors(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('leader.dashboard', ['onglet' => 'signalements']))
            ->assertForbidden();
    }

    public function test_a_pending_deacon_behaves_like_a_member(): void
    {
        $this->actingAs(User::factory()->deacon(LeaderStatus::Pending)->create())
            ->get(route('leader.dashboard'))
            ->assertForbidden();
    }

    public function test_an_approved_deacon_sees_reports(): void
    {
        Report::factory()->create(['concerned_person' => 'Frère Issa Sawadogo']);

        $this->actingAs(User::factory()->deacon()->create())
            ->get(route('leader.dashboard', ['onglet' => 'signalements']))
            ->assertOk()
            ->assertSee('Frère Issa Sawadogo');
    }

    public function test_an_admin_who_is_not_a_leader_does_not_see_reports(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('leader.dashboard'))
            ->assertForbidden();
    }

    public function test_only_an_admin_approves_rights(): void
    {
        $candidate = User::factory()->pastor(LeaderStatus::Pending)->create();

        $this->actingAs(User::factory()->deacon()->create())
            ->patch(route('admin.rights.update', $candidate))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.rights.update', $candidate))
            ->assertRedirect();

        $this->assertTrue($candidate->refresh()->isLeader());
    }

    public function test_announcements_are_reserved_to_tegv_christians(): void
    {
        $this->actingAs(User::factory()->create(['is_tegv_christian' => false]))
            ->get(route('announcements.index'))
            ->assertForbidden();
    }

    public function test_every_member_screen_renders(): void
    {
        $admin = User::factory()->admin()->pastor()->bornToday()->create();

        foreach (['home', 'directory', 'profile.edit', 'notices.create', 'announcements.index', 'announcements.create', 'leader.dashboard', 'admin.rights.index', 'admin.qr-code', 'admin.poster'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        foreach (['validations', 'absences', 'visiteurs', 'signalements'] as $tab) {
            $this->actingAs($admin)->get(route('leader.dashboard', ['onglet' => $tab]))->assertOk();
        }

        $this->actingAs($admin)->get(route('members.show', $admin))->assertOk();
    }
}
