<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitorRequest;
use App\Models\Visitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VisitorFormController extends Controller
{
    public const DEVICE_COOKIE = 'tegv_appareil';

    public function create(Request $request): View
    {
        if (! $request->cookie(self::DEVICE_COOKIE)) {
            Cookie::queue(self::DEVICE_COOKIE, (string) Str::uuid(), 60 * 24 * 365);
        }

        // "Enregistrer une autre personne": same church and number as the previous person, editable.
        $previous = $request->boolean('suite') ? $request->session()->get('visitor.previous') : null;

        return view('visitors.form', ['previous' => $previous]);
    }

    public function store(StoreVisitorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Visitor::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'has_no_church' => $data['has_no_church'],
            'origin_church' => $data['has_no_church'] ? null : $data['origin_church'],
            'whatsapp' => $data['whatsapp'],
            'visited_on' => today(),
            'device_hash' => self::deviceHash($request),
        ]);

        $request->session()->put('visitor.previous', [
            'origin_church' => $data['has_no_church'] ? null : $data['origin_church'],
            'has_no_church' => $data['has_no_church'],
            'whatsapp' => $data['whatsapp'],
        ]);
        $request->session()->increment('visitor.count');

        return redirect()->route('visitors.thanks')->with('visitor_first_name', $data['first_name']);
    }

    public function thanks(Request $request): View
    {
        return view('visitors.thanks', ['registeredCount' => (int) $request->session()->get('visitor.count', 0)]);
    }

    /**
     * Identifies the phone (cookie) or, without cookie, the connection, for the anti-spam limit.
     */
    public static function deviceHash(Request $request): string
    {
        return hash('sha256', $request->cookie(self::DEVICE_COOKIE) ?: $request->ip().'|'.$request->userAgent());
    }
}
