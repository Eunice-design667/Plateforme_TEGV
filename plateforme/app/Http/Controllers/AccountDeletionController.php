<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Account deletion on request: the profile is hidden at once, the member can
 * cancel by logging in again, and everything is deleted after the delay.
 */
class AccountDeletionController extends Controller
{
    public function create(Request $request): View
    {
        return view('profile.delete', ['member' => $request->user()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validateWithBag('deletion', [
            'password' => ['required', 'current_password'],
            'confirmation' => ['accepted'],
        ]);

        $member = $request->user();
        $member->deletion_requested_at = now();
        $member->save();

        $date = $member->deletionDate()->translatedFormat('j F Y');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', "Votre demande est enregistrée : votre compte sera supprimé le {$date}. D’ici là, reconnectez-vous pour annuler.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $member = $request->user();
        $member->deletion_requested_at = null;
        $member->save();

        return redirect()->route('home')->with('status', 'La suppression de votre compte est annulée. Votre profil est de nouveau visible.');
    }
}
