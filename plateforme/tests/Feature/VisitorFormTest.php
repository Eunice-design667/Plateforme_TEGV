<?php

namespace Tests\Feature;

use App\Enums\ThanksStatus;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VisitorFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validVisitor(array $overrides = []): array
    {
        return [
            'first_name' => 'Awa',
            'last_name' => 'Ilboudo',
            'origin_church' => 'Église Baptiste',
            'whatsapp' => '70 26 83 95',
            'consent' => '1',
            ...$overrides,
        ];
    }

    public function test_the_form_is_public(): void
    {
        $this->get(route('visitors.create'))->assertOk()->assertSee('Église d’origine');
    }

    public function test_a_visitor_is_saved_with_todays_date_and_waits_for_thanks(): void
    {
        Carbon::setTestNow('2026-11-01 09:30:00');

        $this->post(route('visitors.store'), $this->validVisitor())
            ->assertRedirect(route('visitors.thanks'));

        $visitor = Visitor::sole();
        $this->assertSame('2026-11-01', $visitor->visited_on->toDateString());
        $this->assertSame(ThanksStatus::Pending, $visitor->thanks_status);
        $this->assertSame('Église Baptiste', $visitor->origin_church);
    }

    public function test_a_visitor_without_church_does_not_need_one(): void
    {
        $this->post(route('visitors.store'), $this->validVisitor(['origin_church' => null, 'has_no_church' => '1']))
            ->assertRedirect(route('visitors.thanks'));

        $this->assertTrue(Visitor::sole()->has_no_church);
    }

    public function test_consent_and_church_are_required(): void
    {
        $this->post(route('visitors.store'), $this->validVisitor(['consent' => null, 'origin_church' => null]))
            ->assertSessionHasErrors(['consent', 'origin_church']);

        $this->assertDatabaseEmpty('visitors');
    }

    public function test_the_same_device_is_limited_per_day(): void
    {
        config(['tegv.visitor_daily_limit' => 2]);

        $this->post(route('visitors.store'), $this->validVisitor())->assertRedirect();
        $this->post(route('visitors.store'), $this->validVisitor())->assertRedirect();
        $this->post(route('visitors.store'), $this->validVisitor())->assertTooManyRequests();

        $this->assertDatabaseCount('visitors', 2);
    }

    public function test_bots_filling_the_hidden_field_are_rejected(): void
    {
        $this->post(route('visitors.store'), $this->validVisitor(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseEmpty('visitors');
    }
}
