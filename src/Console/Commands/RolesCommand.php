<?php

namespace Mate\Roles\Console\Commands;

use App\Models\User;
use Mate\Roles\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mate\Roles\Models\RolePermissions;

class RolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mate:roles {roleName=} {--permissions=} {--list}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure a role with a group of permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('list')) {
            array_walk(Roles::all(), fn (Role $role) => $this->info($role->name));
        }

        $roleName = $this->argument('roleName');
        $permissions = explode(',', $this->option('permissions'));

        $toAssign = array_filter(
            $permissions,
            fn ($permission) => in_array(
                $permission,
                config('roles.permissions')
            )
        );

        $role = Role::firstOrCreate(['name' => $roleName]);
        $this->info("Updating Role: {$role->name}");
        try {
            RolePermissions::updateMatrix([$role->id => $toAssign]);
        } catch (\Exception $e) {
            $this->error($e);
        }
    }

}
