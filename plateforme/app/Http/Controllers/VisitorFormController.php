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

        return view('visitors.form');
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

        return redirect()->route('visitors.thanks')->with('visitor_first_name', $data['first_name']);
    }

    public function thanks(): View
    {
        return view('visitors.thanks');
    }

    /**
     * Identifies the phone (cookie) or, without cookie, the connection, for the anti-spam limit.
     */
    public static function deviceHash(Request $request): string
    {
        return hash('sha256', $request->cookie(self::DEVICE_COOKIE) ?: $request->ip().'|'.$request->userAgent());
    }
}
