<?php

namespace App\Http\Controllers;

use App\Enums\ReportType;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('report', [
            'type' => ['required', Rule::enum(ReportType::class)],
            'concerned_person' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $report = new Report($validated);
        $report->user()->associate($request->user());
        $report->save();

        return redirect()->route('home')->with('status', 'Merci, votre signalement a été transmis aux pasteurs et diacres.');
    }

    /**
     * A pastor or deacon marks the report as taken care of.
     */
    public function update(Request $request, Report $report): RedirectResponse
    {
        if ($report->handled_at && $report->handled_by !== $request->user()->id && ! $request->boolean('reopen')) {
            return back()->with('status', "Ce signalement a déjà été traité par {$report->handler?->full_name}.");
        }

        $report->markHandledBy($report->handled_at ? null : $request->user());

        return back()->with('status', $report->handled_at ? 'Signalement marqué comme traité.' : 'Signalement rouvert.');
    }
}
