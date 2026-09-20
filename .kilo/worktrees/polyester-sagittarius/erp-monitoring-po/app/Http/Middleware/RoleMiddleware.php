<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles)
    {
        $allowed = explode('|', $roles);
        $user = $request->user();
        if (!$user || !$user->roles()->whereIn('slug', $allowed)->exists()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}
