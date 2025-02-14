<?php

namespace Mate\Roles\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;


class PermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mate:permissions {userid} {--permissions=} {--list}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure a User with one or many permissions or even a complete role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('list')) {
          array_walk(config('roles.permissions'), fn (string $permission) => $this->info($permission));
        }

        $permissions = explode(',' , $this->option('permissions'));

        $toAssign = array_filter($permissions, fn ($permission) => in_array($permission, config('roles.permissions')));

        $id = intval($this->argument('userid'));
        $user = User::findOrFail($id);

        $this->info("Updating user: {$user->id}");
        $user->updateUserPermissions($toAssign);
    }
}
