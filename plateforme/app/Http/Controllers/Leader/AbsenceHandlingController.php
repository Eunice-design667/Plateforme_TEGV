<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AbsenceHandlingController extends Controller
{
    /**
     * A pastor or deacon marks an absence as taken care of (or reopens it).
     */
    public function update(Request $request, Absence $absence): RedirectResponse
    {
        if ($absence->handled_at && $absence->handled_by !== $request->user()->id && ! $request->boolean('reopen')) {
            return back()->with('status', "Cette absence a déjà été traitée par {$absence->handler?->full_name}.");
        }

        $absence->markHandledBy($absence->handled_at ? null : $request->user());

        return back()->with('status', $absence->handled_at ? 'Absence marquée comme traitée.' : 'Absence rouverte.');
    }
}
