<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function show(User $member): View
    {
        abort_unless($member->hasCompletedProfile() && ! $member->isPendingDeletion(), 404);

        return view('members.show', [
            'member' => $member,
            'posters' => $member->posters()->latest()->get(),
            // Published information also appears in the member's own profile (F5).
            'announcements' => auth()->id() === $member->id && $member->canSeeAnnouncements()
                ? Announcement::query()->published()->with('validator')->latest('published_at')->limit(3)->get()
                : collect(),
        ]);
    }
}
