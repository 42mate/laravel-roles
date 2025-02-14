<?php

namespace Mate\Roles;

use Illuminate\Support\ServiceProvider;
use Mate\Roles\Console\Commands\PermissionsCommand;
use Mate\Roles\Middleware\HasPermissions;

class RolesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'roles');
        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');

        $this->publishes([
            __DIR__.'/../config/roles.php' => config_path('roles.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/roles'),
        ], 'views');

        $createRoleMigrationFile = database_path('migrations/v1_create_roles_table.php');

        if (! file_exists($createRoleMigrationFile)) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_roles.php.stub' => $createRoleMigrationFile,
            ], 'migrations');
        }


        if ($this->app->runningInConsole()) {
          $this->commands([
            PermissionsCommand::class,
          ]);
        }

    }

    public function register(): void
    {
        $this->app->bind('roles', function () {
            return new Roles();
        });

        app('router')->aliasMiddleware('has-permissions', HasPermissions::class);

        $this->mergeConfigFrom(__DIR__.'/../config/roles.php', 'roles');
    }
}
