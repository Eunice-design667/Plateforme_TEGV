<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visitor;
use App\Support\FrenchDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Bilan v9: no automatic WhatsApp thanks; the visitors of each Sunday are listed and exported.
 */
class VisitorListExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Visitor::factory()->create(['first_name' => 'Awa', 'last_name' => 'Ilboudo', 'origin_church' => 'Église Baptiste', 'visited_on' => '2026-11-15']);
        Visitor::factory()->create(['first_name' => 'Issa', 'last_name' => 'Sawadogo', 'origin_church' => null, 'has_no_church' => true, 'visited_on' => '2026-11-15']);
        Visitor::factory()->create(['first_name' => 'Ancien', 'last_name' => 'Visiteur', 'visited_on' => '2026-11-08']);
    }

    public function test_leaders_pick_a_sunday_and_see_its_visitors(): void
    {
        $deacon = User::factory()->deacon()->create();

        $this->actingAs($deacon)->get(route('leader.dashboard', ['onglet' => 'visiteurs']))
            ->assertSee('Dimanche 15 novembre 2026')
            ->assertSee('Awa Ilboudo')->assertSee('Issa Sawadogo')->assertDontSee('Ancien Visiteur')
            ->assertSee(route('leader.visitors.export', ['format' => 'pdf', 'date' => '2026-11-15']))
            ->assertDontSee('Remerciement');

        $this->actingAs($deacon)->get(route('leader.dashboard', ['onglet' => 'visiteurs', 'date' => '2026-11-08']))
            ->assertSee('Ancien Visiteur')->assertDontSee('Awa Ilboudo');
    }

    public function test_the_pdf_export_lists_the_visitors_of_the_day(): void
    {
        $response = $this->actingAs(User::factory()->pastor()->create())
            ->get(route('leader.visitors.export', ['format' => 'pdf', 'date' => '2026-11-15']));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('visiteurs-tegv-2026-11-15.pdf', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_the_csv_export_opens_in_excel_with_accents(): void
    {
        $csv = $this->actingAs(User::factory()->pastor()->create())
            ->get(route('leader.visitors.export', ['format' => 'csv', 'date' => '2026-11-15']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Prénom;Nom', $csv);
        $this->assertStringContainsString('15/11/2026;Awa;Ilboudo;"Église Baptiste"', $csv);
        $this->assertStringContainsString('Issa;Sawadogo;"Pas d’église"', $csv);
        $this->assertStringNotContainsString('Ancien', $csv);

        $all = $this->actingAs(User::factory()->pastor()->create())->get(route('leader.visitors.export', ['format' => 'csv']))->streamedContent();
        $this->assertStringContainsString('Ancien', $all);
    }

    public function test_members_and_admins_cannot_export(): void
    {
        $this->actingAs(User::factory()->create())->get(route('leader.visitors.export', ['format' => 'pdf']))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('leader.visitors.export', ['format' => 'csv']))->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->get(route('leader.visitors.export', ['format' => 'csv']))->assertRedirect(route('login'));
    }

    public function test_bad_export_parameters_are_refused(): void
    {
        $leader = User::factory()->pastor()->create();

        $this->actingAs($leader)->get(route('leader.visitors.export', ['format' => 'exe']))->assertSessionHasErrors('format');
        $this->actingAs($leader)->get(route('leader.visitors.export', ['format' => 'csv', 'date' => '15/11/2026']))->assertSessionHasErrors('date');
    }

    public function test_no_whatsapp_thanks_remain(): void
    {
        $this->get(route('visitors.create'))->assertDontSee('message de remerciement')->assertSee('enregistre mes coordonnées');
        $this->artisan('list')->doesntExpectOutputToContain('remercier-visiteurs');
        $this->assertNull(config('services.whatsapp'));
    }

    public function test_several_visitors_can_register_on_one_phone(): void
    {
        Carbon::setTestNow('2026-11-22 09:00:00');
        $family = ['has_no_church' => '0', 'origin_church' => 'Église AD Gounghin', 'whatsapp' => '70 26 83 95', 'consent' => '1'];

        $this->post(route('visitors.store'), ['first_name' => 'Awa', 'last_name' => 'Ouédraogo', ...$family])->assertRedirect(route('visitors.thanks'));
        $this->get(route('visitors.thanks'))->assertSee('Enregistrer une autre personne')->assertSee(route('visitors.create', ['suite' => 1]), false);

        // The second person finds the church and number already filled in.
        $this->get(route('visitors.create', ['suite' => 1]))
            ->assertSee('Une autre personne')
            ->assertSee('value="Église AD Gounghin"', false)
            ->assertSee('value="70 26 83 95"', false);

        foreach (['Paul', 'Esther', 'Ruth', 'Daniel'] as $child) {
            $this->post(route('visitors.store'), ['first_name' => $child, 'last_name' => 'Ouédraogo', ...$family])->assertRedirect(route('visitors.thanks'));
        }

        $this->assertSame(5, Visitor::whereDate('visited_on', '2026-11-22')->count());
        $this->get(route('visitors.thanks'))->assertSee('5 personnes enregistrées');
    }

    public function test_the_first_of_the_month_is_written_1er(): void
    {
        $this->assertSame('dimanche 1er novembre 2026', FrenchDate::long(Carbon::parse('2026-11-01')));
        $this->assertSame('dimanche 15 novembre', FrenchDate::long(Carbon::parse('2026-11-15'), false));
    }

    public function test_the_motto_is_soli_deo_gloria(): void
    {
        $this->get('/')->assertSee('Soli Deo Gloria')->assertDontSee('Solo Deo Gloria');
        $this->get(route('login'))->assertSee('Soli Deo Gloria');
        $this->assertSame('Soli Deo Gloria', config('tegv.tagline'));
    }
}
