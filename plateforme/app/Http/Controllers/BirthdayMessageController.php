<?php

namespace App\Http\Controllers;

use App\Models\BirthdayMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BirthdayMessageController extends Controller
{
    public function update(Request $request, BirthdayMessage $birthdayMessage): RedirectResponse
    {
        abort_unless($birthdayMessage->user_id === $request->user()->id, 403);

        $birthdayMessage->read_at = now();
        $birthdayMessage->save();

        return back();
    }
}
