<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\RedirectResponse;

class VisitorController extends Controller
{
    /**
     * Deletes a visitor's data on request, as promised on the visitor form.
     * A visitor deleted before Monday never receives the thank-you message.
     */
    public function destroy(Visitor $visitor): RedirectResponse
    {
        $name = $visitor->fullName();
        $visitor->delete();

        return redirect()
            ->route('leader.dashboard', ['onglet' => 'visiteurs'])
            ->with('status', "Les données de {$name} ont été supprimées.");
    }
}
