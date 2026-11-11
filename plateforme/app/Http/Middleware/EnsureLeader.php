<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLeader
{
    /**
     * Only approved pastors and deacons may continue.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isLeader(), 403, 'Espace réservé aux pasteurs et diacres validés.');

        return $next($request);
    }
}
