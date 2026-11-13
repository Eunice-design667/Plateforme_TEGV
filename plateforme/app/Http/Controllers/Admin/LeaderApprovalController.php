<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeaderStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderApprovalController extends Controller
{
    public function index(): View
    {
        return view('admin.rights', [
            'pending' => User::query()->where('leader_status', LeaderStatus::Pending->value)->oldest('leader_requested_at')->get(),
            'approved' => User::query()->where('leader_status', LeaderStatus::Approved->value)->with('leaderValidator')->orderBy('last_name')->get(),
            'admins' => User::query()->where('is_admin', true)->orderBy('last_name')->get(),
            'deletions' => User::query()->whereNotNull('deletion_requested_at')->oldest('deletion_requested_at')->get(),
        ]);
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        abort_unless($member->declaresLeaderRole(), 422);

        $member->leader_status = LeaderStatus::Approved;
        $member->leaderValidator()->associate($request->user());
        $member->leader_validated_at = now();
        $member->save();

        return back()->with('status', "Les droits de {$member->full_name} sont activés.");
    }

    public function destroy(Request $request, User $member): RedirectResponse
    {
        $member->leader_status = LeaderStatus::Rejected;
        $member->leaderValidator()->associate($request->user());
        $member->leader_validated_at = now();
        $member->save();

        return back()->with('status', "La demande de {$member->full_name} est refusée : le compte reste membre.");
    }
}
