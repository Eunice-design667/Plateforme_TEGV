<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Poster;
use App\Models\User;
use App\Services\BirthdayWishes;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request, BirthdayWishes $wishes): View
    {
        $user = $request->user();
        $today = today();

        $birthdays = User::query()
            ->withCompletedProfile()
            ->celebratingOn($today)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'photo_path']);

        $announcements = $user->canSeeAnnouncements()
            ? Announcement::query()->published()->with('validator')->latest('published_at')->limit(6)->get()
            : collect();

        // Written here too, in case the morning task has not run yet.
        $birthdayMessage = $birthdays->contains('id', $user->id) ? $wishes->ensureFor($user, $today) : null;

        if ($birthdayMessage?->read_at !== null) {
            $birthdayMessage = null;
        }

        return view('home', [
            'user' => $user,
            'birthdays' => $birthdays,
            'announcements' => $announcements,
            'birthdayMessage' => $birthdayMessage,
            'nextService' => $this->nextService(),
            'stories' => $this->stories($user),
        ]);
    }

    /**
     * Recent posters grouped by member, newest first, the viewer's own first.
     *
     * @return list<array{id: int, name: string, initials: string, photo: string|null, isMe: bool, posters: list<array{url: string, caption: string|null, ago: string}>}>
     */
    private function stories(User $viewer): array
    {
        return Poster::query()
            ->inStories()
            ->with('user:id,first_name,last_name,email,photo_path')
            ->latest()
            ->get()
            ->groupBy('user_id')
            ->map(fn ($posters) => [
                'id' => $posters->first()->user->id,
                'name' => $posters->first()->user->first_name ?? $posters->first()->user->full_name,
                'initials' => $posters->first()->user->initials,
                'photo' => $posters->first()->user->photo_url,
                'isMe' => $posters->first()->user_id === $viewer->id,
                'posters' => $posters->sortBy('created_at')->map(fn (Poster $poster) => [
                    'url' => $poster->image_url,
                    'caption' => $poster->caption,
                    'ago' => $poster->created_at->diffForHumans(),
                ])->values()->all(),
            ])
            ->sortByDesc('isMe')
            ->values()
            ->all();
    }

    /**
     * @return array{day: string, title: string, time: string, weekday: int, isToday: bool}
     */
    private function nextService(): array
    {
        $weekday = today()->dayOfWeek;
        $programme = collect(config('tegv.programme'));

        $next = $programme->sortBy(fn (array $item) => ($item['weekday'] - $weekday + 7) % 7)->first();

        return [...$next, 'isToday' => $next['weekday'] === $weekday];
    }
}
