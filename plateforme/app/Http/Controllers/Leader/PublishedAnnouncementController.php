<?php

namespace App\Http\Controllers\Leader;

use App\Enums\AnnouncementStatus;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Review of proposed information: read, edit, then publish or refuse.
 */
class PublishedAnnouncementController extends Controller
{
    public function edit(Announcement $announcement): View
    {
        return view('leader.announcement-edit', ['announcement' => $announcement->load('author')]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->fill($request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:3000'],
        ]));

        if ($announcement->status !== AnnouncementStatus::Published) {
            $announcement->status = AnnouncementStatus::Published;
            $announcement->published_at = now();
            $announcement->validator()->associate($request->user());
        }

        $announcement->save();

        return redirect()->route('leader.dashboard')->with('status', 'Information publiée pour les chrétiens de TEGV.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('leader.dashboard')->with('status', 'Information retirée.');
    }
}
