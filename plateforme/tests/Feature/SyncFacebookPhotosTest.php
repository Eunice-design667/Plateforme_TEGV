<?php

namespace Tests\Feature;

use App\Models\FacebookPhoto;
use App\Services\ChurchPhotos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SyncFacebookPhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_token_the_default_photos_are_kept(): void
    {
        config(['tegv.facebook.token' => null]);
        Http::fake();

        $this->artisan('tegv:photos-facebook')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertContains('/images/eglise/culte.jpg', app(ChurchPhotos::class)->urls());
    }

    public function test_new_page_photos_are_downloaded_once(): void
    {
        Storage::fake('media');
        config(['tegv.facebook.token' => 'page-token', 'tegv.facebook.page_id' => 'Tegv11']);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['data' => [[
                'id' => '1554708720017546',
                'name' => 'Culte du dimanche',
                'created_time' => '2026-11-08T10:00:00+0000',
                'images' => [
                    ['source' => 'https://scontent.example/big.jpg', 'width' => 2048, 'height' => 1365],
                    ['source' => 'https://scontent.example/medium.jpg', 'width' => 960, 'height' => 640],
                ],
            ]]]),
            'scontent.example/*' => Http::response('jpeg-bytes'),
        ]);

        $this->artisan('tegv:photos-facebook')->assertSuccessful();
        $this->artisan('tegv:photos-facebook')->assertSuccessful();

        $photo = FacebookPhoto::sole();
        $this->assertSame(960, $photo->width, 'The largest version under 1600 px is kept.');
        Storage::disk('media')->assertExists('facebook/1554708720017546.jpg');
        Http::assertSentCount(3);
        $this->assertSame($photo->image_url, app(ChurchPhotos::class)->urls()[0]);
    }

    public function test_a_refused_token_is_reported(): void
    {
        config(['tegv.facebook.token' => 'expired']);
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Session has expired']], 400)]);

        $this->artisan('tegv:photos-facebook')
            ->expectsOutputToContain('Session has expired')
            ->assertFailed();
    }
}
