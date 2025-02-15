<?php

namespace Mate\Roles\Console\Commands;

use Closure;
use App\Models\User;
use Illuminate\Console\Command;

class PermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "mate:permissions {--userid=} {--permissions=} {--list} {--append}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Configure a User with one or many permissions or even a complete role";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        [$fn, $args] = $this->dispatch();
        $fn(...$args);
    }

    /**
     * Dispatches the appropriate action based on the command options and arguments.
     *
     * @return array The first element is a callable (as a Closure) for the action,
     *               and the second element is an array of arguments to be passed to the action.
     */
    private function dispatch(): array
    {
        if ($this->option("list")) {
            return [Closure::fromCallable([$this, 'list']), []];
        }

        $permissions = array_map(
            fn(string $permission) => trim($permission),
            explode(",", $this->option("permissions"))
        );

        $toAssign = array_filter(
            $permissions,
            fn($permission) => in_array($permission, config("roles.permissions"))
        );

        $id = intval($this->option("userid"));
        $user = User::findOrFail($id);

        if ($this->option("append")) {
            return [
            Closure::fromCallable([$this, 'updatePermissions']),
            [$user, $toAssign]
            ];
        }

        return [
        Closure::fromCallable([$this, 'setPermissions']),
        [$user, $toAssign]
        ];
    }

    /**
     * Lists all available permissions by printing them to the console.
     *
     * @return void
     */
    private function list()
    {
        $this->info("Available Permissions");

        $permissions = config("roles.permissions");
        array_walk(
            $permissions,
            fn(string $permission) => $this->info($permission)
        );
    }

    /**
     * Sets the given permissions for the specified user.
     *
     * @param User  $user        The user whose permissions will be updated.
     * @param array $permissions The list of permissions to set.
     *
     * @return void
     */
    private function setPermissions(User $user, array $permissions): void
    {
        $user->updateUserPermissions($permissions);
    }

    /**
     * Appends the given permissions to the user's existing permissions.
     *
     * @param User  $user        The user whose permissions will be updated.
     * @param array $permissions The list of permissions to append.
     *
     * @return void
     */
    private function updatePermissions(User $user, array $permissions): void
    {
        $currentPermissions = $user->permissions();

        $newPermissions = array_filter(
            $permissions,
            fn (string $permission) => !($currentPermissions->contains('permission', $permission))
        );

        $user->permissions()->createMany(
            array_map(fn(string $permission) => ['permission' => $permission], $newPermissions)
        );
    }



}
