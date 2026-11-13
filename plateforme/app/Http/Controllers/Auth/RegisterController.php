<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Step 1 of 2. A new account coming back from step 2 sees its email and may correct it.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()?->hasCompletedProfile()) {
            return redirect()->route('home');
        }

        return view('auth.register', ['account' => $request->user()]);
    }

    /**
     * Step 1 of 2: the account. The profile comes next.
     */
    public function store(Request $request): RedirectResponse
    {
        $account = $request->user();

        if ($account?->hasCompletedProfile()) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($account?->id)],
            'password' => [$account ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if ($account) {
            $account->email = $validated['email'];
            $account->contact_email ??= $validated['email'];

            if (filled($validated['password'] ?? null)) {
                $account->password = $validated['password'];
            }

            $account->save();

            return redirect()->route('profile.create');
        }

        $user = User::create([
            'name' => $validated['email'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'contact_email' => $validated['email'],
        ]);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->put('ephemeral', ! $request->boolean('remember'));

        return redirect()->route('profile.create');
    }
}
