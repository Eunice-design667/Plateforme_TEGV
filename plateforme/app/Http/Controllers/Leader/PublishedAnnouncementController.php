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
    public function edit(Request $request, Announcement $announcement): View
    {
        $leader = $request->user();
        $reviewedByAnother = $announcement->isBeingReviewedByAnotherThan($leader);

        // Opening the review marks it as "en cours de relecture" for the other leaders.
        if (! $reviewedByAnother && $announcement->status !== AnnouncementStatus::Published) {
            $announcement->claimed_by = $leader->id;
            $announcement->claimed_at = now();
            $announcement->save();
        }

        return view('leader.announcement-edit', [
            'announcement' => $announcement->load('author', 'claimer', 'validator'),
            'reviewedByAnother' => $reviewedByAnother,
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        if ($announcement->status === AnnouncementStatus::Published && $announcement->validated_by !== $request->user()->id) {
            return redirect()->route('leader.dashboard')->with('status', "Cette information a déjà été publiée par {$announcement->validator?->full_name}.");
        }

        $announcement->fill($request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:3000'],
        ]));

        if ($announcement->status !== AnnouncementStatus::Published) {
            $announcement->status = AnnouncementStatus::Published;
            $announcement->published_at = now();
            $announcement->validator()->associate($request->user());
            $announcement->claimed_by = null;
            $announcement->claimed_at = null;
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
