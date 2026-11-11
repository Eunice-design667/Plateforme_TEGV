<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsenceController extends Controller
{
    /**
     * The "Absence et signalement" screen holds both forms.
     */
    public function create(Request $request): View
    {
        return view('notices.create', [
            'user' => $request->user(),
            'tab' => $request->query('onglet') === 'signaler' ? 'report' : 'absence',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['is_tegv_christian' => $request->boolean('is_tegv_christian')]);

        $validated = $request->validateWithBag('absence', [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'reason' => ['required', 'string', 'max:1000'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'is_tegv_christian' => ['boolean'],
        ]);

        $absence = new Absence($validated);
        $absence->user()->associate($request->user());
        $absence->save();

        return redirect()->route('home')->with('status', 'Merci, les pasteurs et diacres sont prévenus de votre absence.');
    }
}
