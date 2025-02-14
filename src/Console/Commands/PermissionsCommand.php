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
    protected $signature = 'mate:roles {userid=} {--permissions=} {--roles=} {--list}';

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

        $permissions = $this->option('permissions');

        $toAssign = array_filter($permissions, fn ($permission) => in_array($permission, config('roles.permissions')));

        $user = User::findOrFail($this->argument('id'));

        $this->info("Updating user: {$user->id}, settting {join(' ', $toAssign)}");
        $user->roles()->sync($toAssign);
    }
}
