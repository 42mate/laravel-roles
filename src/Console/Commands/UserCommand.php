<?php

namespace Mate\Roles\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Mate\Roles\Models\Role;
use Closure;
use Illuminate\Support\Collection;

class UserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mate:user-role {--userid=} {--roles=} {--list} {--append}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure a User with one or many permissions or even a complete role';

    /**
     * Execute the console command.
     *
     * This method is responsible for determining the action to take based on the provided options.
     * It will either list the roles of a user or assign new roles to the user based on the given arguments.
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
     * This method checks the options passed to the command, including `--list` and `--roles`.
     * It either lists the roles assigned to a user or assigns new roles to the user.
     *
     * @return array The first element is a callable (as a Closure) for the action,
     *               and the second element is an array of arguments to be passed to the action.
     */
    private function dispatch(): array
    {
        $id = intval($this->option('userid'));

        if (!$id) {
            $this->error("Missing required parameter --userid");
            return [];
        }

        $user = User::findOrFail($id);

        if ($this->option('list')) {
            return [Closure::fromCallable([$this, 'list']), [$user]];
        }

        $roles = explode(',', $this->option('roles'));
        $dbRoles = Role::whereIn('name', $roles)
            ->get();

        if ($this->option("append")) {
            return [Closure::fromCallable([$this, 'update']), [$user, $dbRoles]];
        }

        return [Closure::fromCallable([$this, 'set']), [$user, $dbRoles]];
    }

    /**
     * Lists all roles assigned to a user.
     *
     * This method retrieves and prints the list of roles assigned to the user.
     * If the `--list` option is passed, this method is called to display the user’s roles.
     *
     * @param User $user The user whose roles are to be listed.
     *
     * @return void
     */
    private function list(User $user)
    {
        $this->info("The user {$user->id} has the following roles: ");
        $user->roles()->get()->each(fn ($role) => $this->info("\t {$role->name}"));
    }

    /**
     * Assigns roles to a user.
     *
     * This method takes the specified roles, syncs them with the user’s current roles in the database,
     * and prints the updated list of roles assigned to the user.
     * If any error occurs during the process, it will print an error message.
     *
     * @param User       $user  The user to whom roles will be assigned.
     * @param Collection $roles A collection of roles to be assigned to the user.
     *
     * @return void
     */
    private function set(User $user, Collection $roles)
    {
        $this->info("Updating user: {$user->id}");
        try {
            $user->roles()->sync($roles->map(fn (Role $role) => $role['id']));
        } catch (\Exception $e) {
            $this->error($e);
        }

        $this->info("The user {$user->id} has the following roles:");
        $user->roles()->get()->each(fn (Role $role) => $this->info("\t {$role->name}"));
    }

    /**
     * Appends new roles to the existing roles of a user.
     *
     * This method merges the current roles of the user with the newly provided roles
     * and updates the user’s role assignments in the database.
     *
     * @param User       $user  The user whose roles are being updated.
     * @param Collection $roles A collection of roles to be appended to the user.
     *
     * @return void
     */
    private function update(User $user, Collection $roles)
    {
        $mergedRoles = $roles->merge($user->roles()->get());
        return $this->set($user, $mergedRoles);
    }
}
