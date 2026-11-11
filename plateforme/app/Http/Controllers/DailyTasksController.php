<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Called once a day by Vercel Cron (the free plan allows one run per day):
 * birthdays every day, visitor thanks on Monday, Facebook photos on Tuesday.
 * Vercel sends "Authorization: Bearer <CRON_SECRET>".
 */
class DailyTasksController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('tegv.cron_secret');

        abort_if($secret === '' || ! hash_equals("Bearer {$secret}", (string) $request->header('Authorization')), 401);

        $tasks = ['tegv:anniversaires'];

        if (today()->isMonday()) {
            $tasks[] = 'tegv:remercier-visiteurs';
        }

        if (today()->isTuesday()) {
            $tasks[] = 'tegv:photos-facebook';
        }

        $results = [];

        foreach ($tasks as $task) {
            $status = Artisan::call($task);
            $results[$task] = ['status' => $status, 'output' => trim(Artisan::output())];
        }

        return response()->json($results);
    }
}
