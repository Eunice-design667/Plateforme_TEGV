<?php

namespace Tests\Feature;

use App\Models\Poster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PosterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');
    }

    public function test_a_member_adds_a_poster_to_their_profile(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->post(route('posters.store'), [
            'image' => UploadedFile::fake()->image('affiche.jpg', 2400, 3200),
            'caption' => 'Atelier couture ouvert le samedi',
        ])->assertRedirect(route('members.show', $member));

        $poster = Poster::sole();
        Storage::disk('media')->assertExists($poster->image_path);
        [$width, $height] = getimagesizefromstring(Storage::disk('media')->get($poster->image_path));
        $this->assertSame(1600, max($width, $height), 'Large photos are resized for mobile data.');

        $this->actingAs(User::factory()->create())
            ->get(route('members.show', $member))
            ->assertSee('Atelier couture ouvert le samedi');
    }

    public function test_the_number_of_posters_is_limited(): void
    {
        config(['tegv.max_posters' => 1]);
        $member = User::factory()->create();
        Poster::factory()->for($member)->create();

        $this->actingAs($member)->post(route('posters.store'), [
            'image' => UploadedFile::fake()->image('affiche.jpg'),
        ])->assertSessionHasErrors('image', null, 'poster');

        $this->assertDatabaseCount('posters', 1);
    }

    public function test_only_the_owner_or_an_admin_deletes_a_poster(): void
    {
        $poster = Poster::factory()->create();

        $this->actingAs(User::factory()->create())->delete(route('posters.destroy', $poster))->assertForbidden();
        $this->actingAs($poster->user)->delete(route('posters.destroy', $poster))->assertRedirect();

        $this->assertModelMissing($poster);
    }

    public function test_recent_posters_appear_in_the_home_stories(): void
    {
        $recent = Poster::factory()->create(['caption' => 'Concert de la chorale']);
        Poster::factory()->create(['caption' => 'Ancienne affiche', 'created_at' => now()->subDays(config('tegv.story_days') + 1)]);

        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Concert de la chorale')
            ->assertDontSee('Ancienne affiche')
            ->assertSee($recent->user->first_name);
    }
}
