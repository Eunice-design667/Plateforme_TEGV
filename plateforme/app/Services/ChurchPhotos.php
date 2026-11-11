<?php

namespace App\Services;

use App\Models\FacebookPhoto;
use Illuminate\Support\Facades\Cache;

/**
 * Photos of church life shown on the public screens: the latest photos copied from
 * the Facebook page, completed by the photos shipped with the platform.
 */
class ChurchPhotos
{
    /**
     * Photos shipped with the platform, used until enough Facebook photos are synced.
     *
     * @var list<string>
     */
    private const DEFAULTS = [
        '/images/eglise/louange1.jpg',
        '/images/eglise/louange8.jpg',
        '/images/eglise/culte.jpg',
        '/images/eglise/louange9.jpg',
        '/images/eglise/louange4.jpg',
        '/images/eglise/louange5.jpg',
        '/images/eglise/louange7.jpg',
        '/images/eglise/louange3.jpg',
        '/images/eglise/louange6.jpg',
    ];

    /**
     * @return list<string>
     */
    public function urls(int $count = 9): array
    {
        $synced = Cache::remember('church-photos', now()->addHour(), fn () => FacebookPhoto::query()
            ->latest('posted_at')
            ->limit(30)
            ->get()
            ->map(fn (FacebookPhoto $photo) => $photo->image_url)
            ->all());

        return collect($synced)
            ->merge(self::DEFAULTS)
            ->unique()
            ->take($count)
            ->values()
            ->all();
    }

    public static function forget(): void
    {
        Cache::forget('church-photos');
    }
}
