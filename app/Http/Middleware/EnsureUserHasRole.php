<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowed = $user !== null && (
            $user->hasRole(...$roles)
            || (in_array('admin', $roles, true) && $user->isAdminLike())
        );

        abort_if(! $allowed, 403);

        return $next($request);
    }
}
