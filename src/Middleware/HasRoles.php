<?php

namespace Mate\Roles\Middleware;

use Closure;
use Mate\Roles\Facades\Roles;

class HasRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        foreach ($roles as $role) {
            if (Roles::hasRole($role)) {
                return $next($request);
            }
        }

        // If the user does not have any of the required permissions, you can redirect them or return an error response.
        return redirect()
            ->route("home")
            ->with("error", "You do not have permission to access this page.");
    }
}
