<?php

namespace Mate\Roles;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class Roles
{
    /**
     * Checks if the user implements HasPermissions
     *
     * @throws Exception
     */
    protected function implementsHasPermissions(Authenticatable $user): bool
    {
        if (!in_array("Mate\Roles\HasRoles", class_uses($user))) {
            throw new Exception("User model must use HasRoles trait");
        }

        return true;
    }

    /**
     * Retrieves the logged in user or false.
     *
     * @throws Exception
     */
    protected function getUser(): Authenticatable|bool
    {
        $user = Auth::user();

        if (empty($user)) {
            return false;
        }

        $this->implementsHasPermissions($user);

        return $user;
    }

    /**
     * Checks if the user has the passed role
     *
     * @throws Exception
     */
    public function hasRole(string $role): bool
    {
        $user = $this->getUser();
        $superadmin = config("roles.superadmin", "admin");

        return $user->hasRole($superadmin) || $user->hasRole($role);
    }

    /**
     * @throws Exception
     */
    public function hasPermission(string $permission): bool
    {
        $user = $this->getUser();
        $superadmin = config("roles.superadmin", "admin");

        return $user->hasRole($superadmin) || $user->hasPermission($permission);
    }

    /**
     * @throws Exception
     */
    public function hasPermissions(array $permissions): bool
    {
        $user = $this->getUser();
        $superadmin = config("roles.superadmin", "admin");

        return $user->hasRole($superadmin) ||
            $user->hasPermissions($permissions);
    }
}
