<?php

namespace Mate\Roles\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Mate\Roles\Services\PermissionsService;

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

    protected PermissionsService $service;

    public function __construct() {
        $this->service = new PermissionsService();
        parent::__construct();
    }

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
            return [$this->list(...), []];
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
              $this->updatePermissions(...),
              [$user, $toAssign]
            ];
        }

        return [
            $this->setPermissions(...),
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

        $permissions = $this->service->list();
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
        $this->service->updateUserPermissions($user, $permissions);
        $this->showCurrentPermissions($user);
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
        $this->service->updateUserPermissions($user, $permissions);
        $this->showCurrentPermissions($user);
    }

    /**
     * Display the current permissions for a given user.
     *
     * @param User $user The user whose permissions will be displayed.
     *
     * @return void
     */
    private function showCurrentPermissions(User $user): void
    {
        $this->info("User: {$user->id}, Current permission: \n");
        $user->permissions()->get()->each(fn ($permission) => $this->info("\t {$permission->permission}"));
    }

}
