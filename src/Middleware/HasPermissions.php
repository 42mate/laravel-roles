<?php

namespace Mate\Roles\Middleware;

use Closure;
use Mate\Roles\Facades\Roles;

class HasPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$permissions)
    {
        foreach ($permissions as $permission) {
            if (Roles::hasPermission($permission)) {
                return $next($request);
            }
        }

        $redirects = config("roles.redirects.permissions");

        $result = null;
        foreach ($roles as $role) {
            if (array_key_exists($redirects, $role)) {
                $result = redirect()->route($redirects[$role]);
                break;
            }
        }

        if (is_nul($result)) {
            $result = redirect()->route($redirects["default"]);
        }

        return $result->with(
            "error",
            "You do not have permission to access this page."
        );
    }
}
