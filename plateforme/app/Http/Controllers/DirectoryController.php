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

        return view('directory', [
            'members' => $search->search($term, $role),
            'term' => $term,
            'activeRole' => $role,
        ]);
    }
}
