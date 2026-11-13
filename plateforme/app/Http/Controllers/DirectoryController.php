<?php

namespace App\Http\Controllers;

use App\Enums\ChurchRole;
use App\Services\DirectorySearch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectoryController extends Controller
{
    public function __invoke(Request $request, DirectorySearch $search): View
    {
        $role = ChurchRole::tryFrom((string) $request->query('role'));
        $term = $request->string('q')->limit(80, '')->toString();
        $sector = array_key_exists((string) $request->query('secteur'), config('tegv.sectors')) ? (string) $request->query('secteur') : null;

        return view('directory', [
            'members' => $search->search($term, $role, $sector),
            'term' => $term,
            'activeRole' => $role,
            'activeSector' => $sector,
        ]);
    }
}
