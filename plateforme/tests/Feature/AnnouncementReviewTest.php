<?php

namespace Tests\Feature;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_proposal_stays_hidden_until_a_leader_publishes_it(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->post(route('announcements.store'), [
            'title' => 'Journée de salubrité',
            'body' => 'Samedi à 7h au temple.',
        ])->assertRedirect(route('home'));

        $announcement = Announcement::sole();
        $this->actingAs($member)->get(route('home'))->assertDontSee('Journée de salubrité');

        $this->actingAs(User::factory()->deacon()->create())
            ->put(route('leader.announcements.update', $announcement), ['title' => 'Journée de salubrité du temple', 'body' => 'Samedi 14 à 7h.'])
            ->assertRedirect(route('leader.dashboard'));

        $this->assertSame(AnnouncementStatus::Published, $announcement->refresh()->status);
        $this->actingAs($member)->get(route('home'))->assertSee('Journée de salubrité du temple');
    }

    public function test_members_outside_tegv_do_not_see_published_information(): void
    {
        Announcement::factory()->published()->create(['title' => 'Annonce interne']);

        $this->actingAs(User::factory()->create(['is_tegv_christian' => false]))
            ->get(route('home'))
            ->assertDontSee('Annonce interne');
    }
}
