<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileCoverAndSectorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function profile(array $overrides = []): array
    {
        return ['first_name' => 'Awa', 'last_name' => 'Ilboudo', 'primary_profile' => 'Comptabilité', 'sector' => 'conseil', 'whatsapp' => '70 26 83 95', ...$overrides];
    }

    public function test_the_cover_is_neutral_by_default_and_never_a_church_photo(): void
    {
        $member = User::factory()->create(['cover_style' => null]);

        $this->actingAs(User::factory()->create())->get(route('members.show', $member))
            ->assertSee(config('tegv.covers.neutre.css'), false)
            ->assertDontSee('/images/eglise/', false);
    }

    public function test_a_member_chooses_a_cover_style_or_uploads_a_picture(): void
    {
        Storage::fake('media');
        $member = User::factory()->create();

        $this->actingAs($member)->put(route('profile.update'), $this->profile(['cover_style' => 'nuit']))->assertSessionHasNoErrors();
        $this->assertSame('nuit', $member->refresh()->cover_style);
        $this->actingAs($member)->get(route('members.show', $member))->assertSee(config('tegv.covers.nuit.css'), false);

        $this->actingAs($member)->put(route('profile.update'), $this->profile(['cover_style' => 'photo', 'cover' => UploadedFile::fake()->image('paysage.jpg', 1600, 600)]));
        $member->refresh();
        $this->assertSame('photo', $member->cover_style);
        Storage::disk('media')->assertExists($member->cover_path);
        $picture = $member->cover_path;

        // Going back to a colour removes the uploaded picture.
        $this->actingAs($member)->put(route('profile.update'), $this->profile(['cover_style' => 'or']));
        $this->assertNull($member->refresh()->cover_path);
        Storage::disk('media')->assertMissing($picture);

        $this->actingAs($member)->put(route('profile.update'), $this->profile(['cover_style' => 'inconnu']))->assertSessionHasErrors('cover_style');
    }

    public function test_the_sector_is_chosen_from_the_list(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('profile.edit'))->assertSee('Secteur d’activité', false)->assertSee('Santé et action sociale');
        $this->actingAs($member)->put(route('profile.update'), $this->profile(['sector' => null]))->assertSessionHasErrors(['sector' => 'Choisissez votre secteur d’activité dans la liste.']);
        $this->actingAs($member)->put(route('profile.update'), $this->profile(['sector' => 'pas-un-secteur']))->assertSessionHasErrors('sector');

        $this->actingAs($member)->put(route('profile.update'), $this->profile(['sector' => 'sante']));
        $this->assertSame('Santé et action sociale', $member->refresh()->sectorLabel());
    }

    public function test_the_directory_filters_by_sector(): void
    {
        User::factory()->create(['first_name' => 'Infirmière', 'last_name' => 'Test', 'sector' => 'sante']);
        User::factory()->create(['first_name' => 'Maçon', 'last_name' => 'Test', 'sector' => 'btp']);
        $viewer = User::factory()->create(['sector' => 'autre']);

        $this->actingAs($viewer)->get(route('directory'))->assertSee('Tous les secteurs d’activité', false)->assertSee('Infirmière Test')->assertSee('Maçon Test');
        $this->actingAs($viewer)->get(route('directory', ['secteur' => 'sante']))->assertSee('Infirmière Test')->assertDontSee('Maçon Test');
        $this->actingAs($viewer)->get(route('directory', ['secteur' => 'inconnu']))->assertSee('Maçon Test');
        $this->actingAs($viewer)->get(route('directory', ['q' => 'sante']))->assertSee('Infirmière Test');
    }

    public function test_the_sector_of_existing_profiles_is_deduced_from_the_trade(): void
    {
        $member = User::factory()->create(['primary_profile' => 'Plomberie', 'sector' => null]);

        $migration = require collect(glob(database_path('migrations/*_add_cover_and_sector_to_users_table.php')))->first();
        $migration->down();
        $migration->up();

        $this->assertSame('btp', $member->refresh()->sector);
    }
}
