<?php

namespace Tests\Feature;

use App\Enums\ChurchRole;
use App\Models\User;
use App\Services\DirectorySearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_results_are_ranked_by_experience_and_ignore_accents(): void
    {
        $junior = User::factory()->create(['primary_profile' => 'Électricité', 'sector' => 'autre', 'years_experience' => 3]);
        $senior = User::factory()->create(['primary_profile' => 'Électricité', 'sector' => 'autre', 'years_experience' => 20]);
        User::factory()->create(['primary_profile' => 'Couture', 'sector' => 'autre', 'secondary_profiles' => [], 'company' => null, 'job_title' => null]);

        $results = app(DirectorySearch::class)->search('electricite');

        $this->assertSame([$senior->id, $junior->id], $results->pluck('id')->all());
    }

    public function test_main_profile_beats_secondary_profile_at_equal_experience(): void
    {
        $secondary = User::factory()->create(['primary_profile' => 'Vente', 'sector' => 'autre', 'secondary_profiles' => ['Plomberie'], 'years_experience' => 5]);
        $main = User::factory()->create(['primary_profile' => 'Plomberie', 'sector' => 'autre', 'years_experience' => 5]);

        $results = app(DirectorySearch::class)->search('plomberie');

        $this->assertSame([$main->id, $secondary->id], $results->pluck('id')->all());
    }

    public function test_filter_by_role(): void
    {
        $singer = User::factory()->create(['church_roles' => [ChurchRole::Chorale->value]]);
        User::factory()->create(['church_roles' => []]);

        $this->assertSame([$singer->id], app(DirectorySearch::class)->search(null, ChurchRole::Chorale)->pluck('id')->all());
    }

    public function test_incomplete_profiles_are_hidden(): void
    {
        User::factory()->withoutProfile()->create();

        $this->assertCount(0, app(DirectorySearch::class)->search(null));
    }
}
