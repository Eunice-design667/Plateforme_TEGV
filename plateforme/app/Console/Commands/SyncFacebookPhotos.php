<?php

namespace App\Console\Commands;

use App\Models\FacebookPhoto;
use App\Services\ChurchPhotos;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Copies the latest photos of the church's Facebook page through the official Graph API.
 *
 * Needs FACEBOOK_PAGE_TOKEN: a Page access token created by an administrator of the page
 * (Meta for Developers > app > Graph API Explorer, permissions pages_read_engagement
 * and pages_read_user_content, then exchanged for a long-lived token).
 */
#[Signature('tegv:photos-facebook {--limit=25 : Nombre de photos récentes à lire}')]
#[Description('Récupère les dernières photos de la page Facebook de l’église')]
class SyncFacebookPhotos extends Command
{
    public function handle(): int
    {
        $config = config('tegv.facebook');

        if (blank($config['token'])) {
            $this->warn('FACEBOOK_PAGE_TOKEN est vide : les photos par défaut restent affichées.');

            return self::SUCCESS;
        }

        try {
            $response = Http::baseUrl("https://graph.facebook.com/{$config['version']}")
                ->connectTimeout(5)
                ->timeout(30)
                ->retry([500, 2000], throw: false)
                ->get("/{$config['page_id']}/photos", [
                    'type' => 'uploaded',
                    'fields' => 'id,images,name,created_time',
                    'limit' => (int) $this->option('limit'),
                    'access_token' => $config['token'],
                ]);
        } catch (ConnectionException $exception) {
            $this->error('Facebook injoignable : '.$exception->getMessage());

            return self::FAILURE;
        }

        if ($response->failed()) {
            $this->error('Facebook a refusé la demande : '.($response->json('error.message') ?? $response->status()));

            return self::FAILURE;
        }

        $added = 0;

        foreach ($response->json('data', []) as $photo) {
            if ($this->import($photo)) {
                $added++;
            }
        }

        $this->prune($config['keep']);
        ChurchPhotos::forget();
        $this->info("{$added} nouvelle(s) photo(s) importée(s).");

        return self::SUCCESS;
    }

    /**
     * @param  array{id: string, images?: list<array{source: string, width: int, height: int}>, name?: string, created_time?: string}  $photo
     */
    private function import(array $photo): bool
    {
        if (FacebookPhoto::query()->where('facebook_id', $photo['id'])->exists()) {
            return false;
        }

        // Largest version no wider than 1600 px, to keep pages light.
        $image = collect($photo['images'] ?? [])
            ->sortByDesc('width')
            ->first(fn (array $candidate) => $candidate['width'] <= 1600) ?? collect($photo['images'] ?? [])->last();

        if ($image === null) {
            return false;
        }

        try {
            $download = Http::connectTimeout(5)->timeout(30)->get($image['source']);
        } catch (ConnectionException) {
            return false;
        }

        if ($download->failed()) {
            return false;
        }

        $path = "facebook/{$photo['id']}.jpg";
        Storage::disk('media')->put($path, $download->body());

        FacebookPhoto::create([
            'facebook_id' => $photo['id'],
            'image_path' => $path,
            'caption' => $photo['name'] ?? null,
            'width' => $image['width'],
            'height' => $image['height'],
            'posted_at' => isset($photo['created_time']) ? Carbon::parse($photo['created_time']) : now(),
        ]);

        return true;
    }

    private function prune(int $keep): void
    {
        FacebookPhoto::query()
            ->latest('posted_at')
            ->skip($keep)
            ->take(PHP_INT_MAX)
            ->get()
            ->each(function (FacebookPhoto $photo): void {
                Storage::disk('media')->delete($photo->image_path);
                $photo->delete();
            });
    }
}
