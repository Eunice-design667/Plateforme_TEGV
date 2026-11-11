<?php

namespace App\Http\Controllers;

use App\Services\ChurchPhotos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(Request $request, ChurchPhotos $photos): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('home');
        }

        return view('welcome', ['photos' => $photos->urls(9)]);
    }
}
