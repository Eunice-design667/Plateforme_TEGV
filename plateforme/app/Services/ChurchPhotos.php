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
     * Photos shipped with the platform (chosen from the church's Facebook page),
     * used until enough Facebook photos are synced.
     */
    private const DEFAULT_COUNT = 36;

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
            ->merge(self::defaults())
            ->unique()
            ->take($count)
            ->values()
            ->all();
    }

    /**
     * The shipped photos in a different order each day, so that all of them are shown in turn.
     *
     * @return list<string>
     */
    public static function defaults(): array
    {
        return collect(range(1, self::DEFAULT_COUNT))
            ->map(fn (int $number) => sprintf('/images/eglise/assemblee-%02d.jpg', $number))
            ->shuffle(crc32(today()->toDateString()))
            ->values()
            ->all();
    }

    public static function forget(): void
    {
        Cache::forget('church-photos');
    }
}
