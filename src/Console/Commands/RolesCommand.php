<?php

namespace Mate\Roles\Console\Commands;

use App\Models\User;
use Mate\Roles\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mate\Roles\Models\RolePermissions;
use Closure;

class RolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mate:roles {--roleName=} {--permissions=} {--list} {--describe} {--append}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure a role with a group of permissions';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        [$fn, $args] = $this->dispatch();
        $fn(...$args);
    }

    /**
     * Dispatches the appropriate action based on the command options and arguments.
     *
     * This method determines which action to perform based on the provided options.
     * It can list roles, describe a specific role, update a role's permissions, or append new permissions to an existing role.
     *
     * @return array The first element is a callable (as a Closure) for the action,
     *               and the second element is an array of arguments to be passed to the action.
     */
    private function dispatch(): array
    {
        if ($this->option("list")) {
            return [Closure::fromCallable([$this, 'list']), []];
        }

        $roleName = $this->option('roleName');

        if ($this->option('describe')) {
            $role = Role::where('name', $roleName)->firstOrFail(['name' => $roleName]);
            return [Closure::fromCallable([$this, 'describe']), [$role]];
        }

        $toAssign = $this->parsePermissions();

        $role = Role::firstOrCreate(['name' => $roleName]);

        if ($this->option('append')) {
            return [Closure::fromCallable([$this, 'append']), [$role, $toAssign]];
        }

        return [Closure::fromCallable([$this, 'update']), [$role, $toAssign]];
    }

    /**
     * Lists all available roles by printing them to the console.
     *
     * @return void
     */
    private function list()
    {
        $this->info("Available Permissions");
        Role::all()->each(fn (Role $role) => $this->info("\t {$role->name}"));
    }

    /**
     * Describes the permissions assigned to a specific role.
     *
     * @param Role $role The role whose permissions are to be displayed.
     *
     * @return void
     */
    private function describe(Role $role)
    {
        $this->info("The role {$role->name} has the following permissions:");
        $role->permissions()->get()->each(fn ($permission) => $this->info("\t {$permission->permission}"));
    }

    /**
     * Updates the permissions for a specific role.
     *
     * This method updates the permissions for a given role in the database. If an error occurs during the update process,
     * it will print an error message. Otherwise, it will print the updated permissions to the console.
     *
     * @param Role  $role        The role to update.
     * @param array $permissions The list of permissions to assign to the role.
     *
     * @return void
     */
    private function update(Role $role, array $permissions)
    {
        $this->info("Updating Role: {$role->name}");
        try {
            RolePermissions::updateMatrix([$role->id => $permissions]);
        } catch (\Exception $e) {
            $this->error($e);
            return;
        }

        $this->info("Updated Role: {$role->name}");
        array_walk($permissions, fn ($permission) => $this->info("\t {$permission}"));
    }

    /**
     * Parses the permissions passed as a command option.
     *
     * This method validates the permissions against the configuration and filters out invalid ones.
     *
     * @return array The list of valid permissions.
     */
    private function parsePermissions(): array
    {
        $permissions = explode(',', $this->option('permissions'));

        return array_filter(
            $permissions,
            fn ($permission) => in_array(
                $permission,
                config('roles.permissions')
            )
        );
    }

    /**
     * Appends new permissions to an existing role without overwriting the existing ones.
     *
     * This method merges the new permissions with the existing ones and updates the role in the database.
     *
     * @param Role  $role        The role to update.
     * @param array $permissions The new permissions to append.
     *
     * @return void
     */
    private function append(Role $role, array $permissions)
    {
        $toAssign = array_merge(
            $role->permissions()->get()->map(fn ($permission) => $permission['permission'])->toArray(),
            $permissions
        );

        $this->update($role, $toAssign);
    }
}
