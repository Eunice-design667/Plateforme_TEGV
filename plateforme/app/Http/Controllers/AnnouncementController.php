<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * Published information is reserved to the christians of TEGV.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()->canSeeAnnouncements(), 403, 'Les informations de l’église sont réservées aux chrétiens de TEGV.');

        return view('announcements.index', [
            'announcements' => Announcement::query()->published()->with('validator')->latest('published_at')->paginate(12),
        ]);
    }

    public function create(Request $request): View
    {
        return view('announcements.create', [
            'myProposals' => $request->user()->announcements()->latest()->limit(5)->get(),
        ]);
    }

    /**
     * Any member may propose; a pastor or deacon reviews before publication.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $announcement = new Announcement($validated);
        $announcement->author()->associate($request->user());
        $announcement->save();

        return redirect()->route('home')->with('status', 'Merci ! Votre information sera relue par un pasteur ou un diacre avant publication.');
    }
}
