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
        foreach ($permissions as $permission) {
            if (array_key_exists($permission, $redirects)) {
                $result = redirect()->route($redirects[$permission]);
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
