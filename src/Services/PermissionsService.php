<?php

namespace Mate\Roles\Services;

use Illuminate\Support\Collection;
use Mate\Roles\Models\Role;
use Mate\Roles\Models\RolePermissions;
use App\Models\User;

/**
 * Service class to manage permissions, roles, and role-permissions matrix.
 */
class PermissionsService
{
    /**
     * Middleware configuration for permission-related actions.
     *
     * @var array
     */
    private array $middlewares = [
        'auth',
        'has-permissions:manage permissions'
    ];

    /**
     * Retrieves the list of middlewares.
     *
     * @return array The array of middleware names.
     */
    public function middlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Sets the middleware array to a new list of middlewares.
     *
     * @param array $middlewares The array of new middleware names.
     * @return void
     */
    public function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Appends additional middlewares to the existing middleware list.
     *
     * @param array $middlewares The array of middleware names to add.
     * @return void
     */
    public function appendMiddlewares(array $middlewares): void
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
    }

    /**
     * Retrieves the list of permissions from the configuration.
     *
     * @param bool $asArray Whether to return the permissions as an array. Defaults to `false`.
     * @return Collection|array The list of permissions as a `Collection` or `array`.
     */
    public function list(bool $asArray = false): Collection|array
    {
        $permissions = config('roles.permissions');
        if ($asArray) {
            return $permissions;
        }
        return collect($permissions);
    }

    /**
     * Retrieves the list of roles from the database.
     *
     * @param bool $asArray Whether to return the roles as an array. Defaults to `false`.
     * @return Collection|array The list of roles as a `Collection` or `array`.
     */
    public function listRoles(bool $asArray = false): Collection|array
    {
        $roles = Role::all();
        if ($asArray) {
            return $roles->toArray();
        }
        return $roles;
    }

    /**
     * Retrieves the role-permissions matrix from the RolePermissions model.
     *
     * @return array The role-permissions matrix as an associative array.
     */
    public function rolePermissionsMatrix(): array
    {
        return RolePermissions::getMatrix();
    }

    /**
     * Retrieves a comprehensive dataset of permissions, roles, and the role-permissions matrix.
     *
     * @return array The associative array containing:
     *               - 'permissions': The list of permissions (as an array).
     *               - 'roles': The list of roles (as an array).
     *               - 'role_permissions': The role-permissions matrix.
     */
    public function matrixData(): array
    {
        return [
            'permissions'      => $this->list(true),
            'roles'            => $this->listRoles(true),
            'role_permissions' => $this->rolePermissionsMatrix(),
        ];
    }

    /**
     * Assigns a set of permissions to a user.
     *
     * @param User  $user        The user to assign permissions to.
     * @param array $permissions The array of permissions to assign.
     * @return void
     */
    public function setUserPermssions(User $user, array $permissions): void
    {
        $toSet = array_filter($permissions, fn(string $permission) => in_array($permission, $this->list(asArray: true)));
        $user->updateUserPermissions($toSet);
    }

    /**
     * Updates a user's permissions by adding new valid permissions.
     *
     * @param User  $user        The user to update permissions for.
     * @param array $permissions The array of permissions to update.
     * @return void
     */
    public function updateUserPermissions(User $user, array $permissions): void
    {
        $validPermissions = array_filter($permissions, fn(string $permission) => in_array($permission, $this->list(asArray: true)));
        $currentPermissions = $user->permissions()->get();
        $newPermissions = array_filter(
            $validPermissions,
            fn(string $permission) => !$currentPermissions->contains('permission', $permission)
        );

        $user->permissions()->createMany(
            array_map(fn(string $permission) => ['permission' => $permission], $newPermissions)
        );
    }

    /**
     * Sets a list of permissions for a role.
     *
     * @param Role  $role        The role to set permissions for.
     * @param array $permissions The array of permissions to assign to the role.
     * @return void
     */
    public function setRolePermissions(Role $role, array $permissions): void
    {
        RolePermissions::updateMatrix(
            [
                $role->id => array_reduce(
                    $permissions,
                    function ($output, $permission) {
                        $output[$permission] = true;
                        return $output;
                    },
                    []
                )
            ]
        );
    }

    /**
     * Updates a role's permissions by adding new ones.
     *
     * @param Role  $role        The role to update.
     * @param array $permissions The array of new permissions to assign to the role.
     * @return void
     */
    public function updateRolePermissions(Role $role, array $permissions): void
    {
        $toAssign = array_merge(
            $role->permissions()->get()->map(fn($permission) => $permission['permission'])->toArray(),
            $permissions
        );

        $this->setRolePermissions($role, $toAssign);
    }

    /**
     * Sets roles for a user by syncing with the given collection of roles.
     *
     * @param User       $user  The user to assign roles to.
     * @param Collection $roles The collection of roles to assign to the user.
     * @return void
     */
    public function setUserRoles(User $user, Collection $roles): void
    {
        $user->roles()->sync($roles->map(fn(Role $role) => $role['id']));
    }

    /**
     * Updates a user's roles by appending new roles to their existing ones.
     *
     * @param User       $user  The user to update roles for.
     * @param Collection $roles The collection of roles to add to the user's existing roles.
     * @return void
     */
    public function updateUserRoles(User $user, Collection $roles): void
    {
        $mergedRoles = $roles->merge($user->roles()->get());
        $this->setUserRoles($user, $mergedRoles);
    }
}
