<?php

namespace Tests\Feature;

use App\Models\BirthdayMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VercelHostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_tasks_need_the_cron_secret(): void
    {
        config(['tegv.cron_secret' => 'secret-de-test']);

        $this->get(route('daily-tasks'))->assertUnauthorized();
        $this->get(route('daily-tasks'), ['Authorization' => 'Bearer mauvais'])->assertUnauthorized();

        config(['tegv.cron_secret' => null]);
        $this->get(route('daily-tasks'), ['Authorization' => 'Bearer '])->assertUnauthorized();
    }

    public function test_daily_tasks_run_by_weekday(): void
    {
        config(['tegv.cron_secret' => 'secret-de-test']);
        $headers = ['Authorization' => 'Bearer secret-de-test'];

        Carbon::setTestNow('2026-11-16 10:00:00'); // Monday
        $member = User::factory()->create(['first_name' => 'Esther', 'birth_day' => 16, 'birth_month' => 11]);

        $this->get(route('daily-tasks'), $headers)
            ->assertOk()
            ->assertJsonStructure(['tegv:anniversaires', 'tegv:remercier-visiteurs'])
            ->assertJsonMissingPath('tegv:photos-facebook');
        $this->assertSame($member->id, BirthdayMessage::sole()->user_id);

        Carbon::setTestNow('2026-11-17 10:00:00'); // Tuesday
        $this->get(route('daily-tasks'), $headers)
            ->assertOk()
            ->assertJsonStructure(['tegv:anniversaires', 'tegv:photos-facebook'])
            ->assertJsonMissingPath('tegv:remercier-visiteurs');
    }

    public function test_deploy_adds_demo_data_only_once(): void
    {
        Storage::fake('media');
        config(['tegv.demo_seed' => true, 'tegv.demo_password' => 'Demo-Secret-2026']);

        Artisan::call('tegv:deploy');
        $count = User::count();
        Artisan::call('tegv:deploy');

        $this->assertGreaterThan(10, $count);
        $this->assertSame($count, User::count());
        $this->assertTrue(Hash::check('Demo-Secret-2026', User::where('email', 'admin@tegv.test')->value('password')));
        Storage::disk('media')->assertExists('affiches/demo-banniere.jpg');
    }

    public function test_deploy_without_demo_keeps_the_platform_empty(): void
    {
        config(['tegv.demo_seed' => false]);

        Artisan::call('tegv:deploy');

        $this->assertSame(0, User::count());
    }
}
