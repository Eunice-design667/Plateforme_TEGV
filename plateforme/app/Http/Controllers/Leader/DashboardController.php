<?php

namespace App\Http\Controllers\Leader;

use App\Enums\LeaderStatus;
use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Announcement;
use App\Models\Report;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const TABS = ['validations', 'absences', 'visiteurs', 'signalements'];

    public function __invoke(Request $request): View
    {
        $tab = in_array($request->query('onglet'), self::TABS, true) ? $request->query('onglet') : 'validations';

        return view('leader.dashboard', [
            'tab' => $tab,
            'counts' => [
                'validations' => Announcement::query()->proposed()->count(),
                'absences' => Absence::query()->whereDate('ends_on', '>=', today())->whereNull('handled_at')->count(),
                'visiteurs' => Visitor::query()->whereDate('visited_on', '>=', today()->subDays(7))->count(),
                'signalements' => Report::query()->whereNull('handled_at')->count(),
                'droits' => User::query()->where('leader_status', LeaderStatus::Pending->value)->count(),
            ],
            'proposals' => $tab === 'validations'
                ? Announcement::query()->proposed()->with('author', 'claimer')->oldest()->get()
                : collect(),
            'absences' => $tab === 'absences'
                ? Absence::query()->with('user', 'handler')->whereDate('ends_on', '>=', today()->subDays(30))->orderByRaw('handled_at is not null')->orderBy('starts_on')->get()
                : collect(),
            'visitors' => $tab === 'visiteurs'
                ? Visitor::query()->latest('visited_on')->latest('id')->paginate(30)->withQueryString()
                : collect(),
            'reports' => $tab === 'signalements'
                ? Report::query()->with('user', 'handler')->orderByRaw('handled_at is not null')->latest()->get()
                : collect(),
        ]);
    }
}
