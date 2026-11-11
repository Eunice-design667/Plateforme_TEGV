<?php

namespace App\Http\Controllers;

use App\Enums\LeaderStatus;
use App\Http\Requests\StoreProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Step 2 of 2 of registration.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasCompletedProfile()) {
            return redirect()->route('profile.edit');
        }

        return view('profile.form', ['member' => $request->user(), 'isCreation' => true]);
    }

    public function edit(Request $request): View
    {
        return view('profile.form', ['member' => $request->user(), 'isCreation' => false]);
    }

    public function update(StoreProfileRequest $request): RedirectResponse
    {
        $member = $request->user();
        $isCreation = ! $member->hasCompletedProfile();

        $member->fill($request->safe()->except('photo'));
        $member->name = trim("{$member->first_name} {$member->last_name}");

        if ($request->hasFile('photo')) {
            if ($member->photo_path) {
                Storage::disk('media')->delete($member->photo_path);
            }

            $member->photo_path = $request->file('photo')->store('photos', 'media');
        }

        $this->syncLeaderStatus($member);
        $member->profile_completed_at ??= now();
        $member->save();

        return redirect()
            ->route($isCreation ? 'home' : 'members.show', $isCreation ? [] : $member)
            ->with('status', $isCreation ? 'Bienvenue ! Votre profil est créé.' : 'Votre profil est à jour.');
    }

    /**
     * Declaring yourself pastor or deacon puts the account on hold until an admin approves it.
     */
    private function syncLeaderStatus(User $member): void
    {
        if (! $member->declaresLeaderRole()) {
            $member->leader_status = LeaderStatus::None;
            $member->leader_validated_by = null;
            $member->leader_validated_at = null;

            return;
        }

        if (in_array($member->leader_status, [LeaderStatus::None, LeaderStatus::Rejected], true)
            && $member->isDirty('church_roles')) {
            $member->leader_status = LeaderStatus::Pending;
            $member->leader_requested_at = now();
        }
    }
}
