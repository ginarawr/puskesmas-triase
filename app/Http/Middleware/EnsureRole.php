<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return redirect($user ? $user->homeUrl() : route('login'))
                ->with('error', 'Anda tidak punya akses ke halaman itu.');
        }

        return $next($request);
    }
}
