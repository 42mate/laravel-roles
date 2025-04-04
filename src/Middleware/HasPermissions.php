<?php

namespace Mate\Roles\Middleware;

use Closure;
use Mate\Roles\Facades\Roles;
use Illuminate\Support\Facades\Auth;

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

        $user = Auth::user();

        if (empty($user)) {
            return false;
        }

        $result = null;
        foreach ($user->permissions()->get() as $permission) {
            if (array_key_exists($permission->permission, $redirects)) {
                $result = redirect()->route($redirects[$permission->name]);
                break;
            }
        }
        if (is_null($result)) {
            $result = redirect()->route($redirects["default"]);
        }

        return $result->with(
            "error",
            "You do not have permission to access this page."
        );
    }
}
