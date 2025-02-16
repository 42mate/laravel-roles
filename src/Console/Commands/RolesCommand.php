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
    protected $signature = 'mate:roles {--roleName=} {--permissions=} {--list}';

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
     * This method checks whether the 'list' option is provided. If so, it returns the closure for listing all roles.
     * If not, it creates or fetches a role, validates and filters the permissions, and returns the closure to update the role's permissions.
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
        $permissions = explode(',', $this->option('permissions'));

        $toAssign = array_filter(
            $permissions,
            fn ($permission) => in_array(
                $permission,
                config('roles.permissions')
            )
        );

        $role = Role::firstOrCreate(['name' => $roleName]);
        return [Closure::fromCallable([$this, 'update']), [$role, $toAssign]];
    }

    /**
     * Lists all available permissions by printing them to the console.
     *
     * This method iterates through all roles and prints their names to the console.
     *
     * @return void
     */
    private function list()
    {
        $this->info("Available Permissions");
        Role::all()->each(fn (Role $role) => $this->info("\t {$role->name}"));
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
}
