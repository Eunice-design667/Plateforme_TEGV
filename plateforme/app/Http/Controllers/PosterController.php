<?php

namespace App\Http\Controllers;

use App\Models\Poster;
use App\Support\ImageResizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PosterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('poster', [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'caption' => ['nullable', 'string', 'max:200'],
        ]);

        $member = $request->user();

        if ($member->posters()->count() >= config('tegv.max_posters')) {
            throw ValidationException::withMessages([
                'image' => 'Vous avez atteint '.config('tegv.max_posters').' affiches : supprimez-en une pour en ajouter une nouvelle.',
            ])->errorBag('poster');
        }

        $poster = new Poster(['caption' => $validated['caption'] ?? null]);
        $poster->image_path = ImageResizer::storeJpeg($request->file('image'), 'affiches');
        $member->posters()->save($poster);

        return redirect()->route('members.show', $member)->with('status', 'Votre affiche est publiée sur votre profil et dans les affiches du moment.');
    }

    public function destroy(Request $request, Poster $poster): RedirectResponse
    {
        abort_unless($poster->user_id === $request->user()->id || $request->user()->is_admin, 403);

        Storage::disk('media')->delete($poster->image_path);
        $poster->delete();

        return back()->with('status', 'Affiche supprimée.');
    }
}
