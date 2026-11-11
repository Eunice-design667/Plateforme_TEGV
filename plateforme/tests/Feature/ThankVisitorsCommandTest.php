<?php

namespace Tests\Feature;

use App\Enums\ThanksStatus;
use App\Models\BirthdayMessage;
use App\Models\User;
use App\Models\Visitor;
use App\Services\WhatsApp\WhatsAppException;
use App\Services\WhatsApp\WhatsAppManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class ThankVisitorsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_pending_visitor_is_thanked_once(): void
    {
        $visitor = Visitor::factory()->create(['first_name' => 'Awa', 'whatsapp' => '70 26 83 95']);
        Visitor::factory()->create(['thanks_status' => ThanksStatus::Sent]);

        $this->mock(WhatsAppManager::class, function (MockInterface $mock): void {
            $mock->expects('send')->once()->withArgs(fn (string $number, string $message) => $number === '22670268395' && str_contains($message, 'Awa'))->andReturn('log');
        });

        $this->artisan('tegv:remercier-visiteurs')->assertSuccessful();
        $this->artisan('tegv:remercier-visiteurs')->expectsOutput('Aucun visiteur à remercier.');

        $visitor->refresh();
        $this->assertSame(ThanksStatus::Sent, $visitor->thanks_status);
        $this->assertNotNull($visitor->thanks_sent_at);
    }

    public function test_an_invalid_number_is_marked_failed_without_retry(): void
    {
        $visitor = Visitor::factory()->create(['whatsapp' => '123']);

        $this->mock(WhatsAppManager::class, fn (MockInterface $mock) => $mock->shouldNotReceive('send'));

        $this->artisan('tegv:remercier-visiteurs')->assertSuccessful();

        $this->assertSame(ThanksStatus::Failed, $visitor->refresh()->thanks_status);
    }

    public function test_a_delivery_error_is_recorded(): void
    {
        $visitor = Visitor::factory()->create(['whatsapp' => '70 26 83 95']);

        $this->mock(WhatsAppManager::class, function (MockInterface $mock): void {
            $mock->expects('send')->andThrow(new WhatsAppException('Passerelle WhatsApp injoignable'));
        });

        $this->artisan('tegv:remercier-visiteurs')->assertSuccessful();

        $visitor->refresh();
        $this->assertSame(ThanksStatus::Failed, $visitor->thanks_status);
        $this->assertStringContainsString('injoignable', $visitor->thanks_error);
    }

    public function test_the_gateway_falls_back_to_the_official_api(): void
    {
        config([
            'services.whatsapp.driver' => 'gateway',
            'services.whatsapp.fallback' => 'log',
            'services.whatsapp.gateway.url' => 'http://gateway.test',
        ]);
        Http::fake(['gateway.test/*' => Http::response('blocked', 503)]);

        $this->assertSame('log', app(WhatsAppManager::class)->send('22670268395', 'Bonjour'));
    }

    public function test_birthday_wishes_are_written_once_per_day(): void
    {
        $member = User::factory()->bornToday()->create(['first_name' => 'Esther']);
        User::factory()->create(['birth_day' => today()->addDays(3)->day, 'birth_month' => today()->addDays(3)->month]);

        $this->artisan('tegv:anniversaires')->assertSuccessful();
        $this->artisan('tegv:anniversaires')->assertSuccessful();

        $message = BirthdayMessage::sole();
        $this->assertSame($member->id, $message->user_id);
        $this->assertStringContainsString('Esther', $message->message);
    }
}
