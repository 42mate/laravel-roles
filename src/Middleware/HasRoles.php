<?php

namespace Mate\Roles\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Mate\Roles\Facades\Roles;

class HasRoles
{
    public string $redirectTo = "index";

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

        $redirects = config("roles.redirects.roles");
        $result = null;
        $user = Auth::user();

        if (empty($user)) {
            return false;
        }

        foreach ($user->roles()->get() as $role) {
            if (array_key_exists($role->name, $redirects)) {
                $result = redirect()->route($redirects[$role->name]);
                break;
            }
        }

        if (is_null($result)) {
            $result = redirect()->route($redirects["default"]);
        }

        $flashConfig = config('roles.flash');
        $message = $flashConfig['messages']['permission_denied'];
        
        if ($flashConfig['enabled'] && $flashConfig['format'] === 'inertia') {
            return $result->with('flash.error', $message);
        } 
        
        return $result->with('error', $message);
        
    }
}
