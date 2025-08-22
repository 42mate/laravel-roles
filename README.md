# Mate Roles

Mate Roles enhances the user model by incorporating a comprehensive Roles and Permissions system.

This package seamlessly integrates with your Laravel application, extending the user model
to efficiently manage user roles and permissions. By using this package, you can easily assign
roles to users and define specific permissions for each role, ensuring a secure and organized
access control system.

# Key Features:

- Role Management: Easily create and manage user roles for better organization and control.

- Permission Control: Define granular permissions for each role to restrict user access as needed.

- Minimal Integration: Effortlessly incorporate the package into your existing Laravel application without extensive modifications.

- Developer-Friendly: Intuitive APIs simplify role and permission management tasks.

- Scalable: Designed for performance, even in large-scale applications.

## Installation

You can install the package via composer:

```bash
    composer require mate/roles
```

After that run the vendor publish to publish the configs, migrations, and views.

```bash
    php artisan vendor:publish --tag="config"
    php artisan vendor:publish --tag="migrations"
    php artisan vendor:publish --tag="views"
```

Run the migration to create the Roles tables

```bash
    php artisan migrate
```

The package provides a `Mate\Roles\Traits\HasRoles` trait, you need to add it into your `App\User` model.

```php
    use Mate\Roles\Traits\HasRoles;

    class User extends Authenticatable
    {
        use HasRoles;
    }
```

That's it, you are ready to use it.

## Usage

First, you need to configure the possible permissions in the role.php config.

This is an array of strings with the defined permissions such as `manage users`, `edit posts`, `delete posts`, `access settings`, whatever is needed in your solution.

Each entry is a permission that can be associated with a given role. So for example we can have a role `Sales` and other `Delivery`, the role `Sales` can have permissions to `manage customers` and `create order`, the `Delivery` role can have permissions to `update order status`, `view assigned orders`, etc.

Roles are assigned to users. Agustin can have the role `Sales` and Marcos the role `Delivery`, while Ezequiel has `Sales` and `Delivery`.

You can access to `/permissions` url to visualize the permissions matrix and update the permissions associated to each Role.

Accessing to `/permissions/users/{user}` you can assign the roles to the user.
Or you can implement your own logic to assign roles to users in your application.

### PHP

From the user model, you can check if the user has a given permission:

```php
    $user->hasPermission('permission-name');
```

Or if it has permissions:

```php
    $user->hasPermissions(['permission-name-1', 'permission-name-2']);
```

Or if it has a given role:

```php
    $user->hasRole('role-name');
```

You can also use the facade:

```php
    Mate\Roles\Facades\Roles::hasPermission('permission-name');
    Mate\Roles\Facades\Roles::hasPermissions(['permission-name-1', 'permission-name-2']);
    Mate\Roles\Facades\Roles::hasRole('role-name');
```

The facade will try it against the logged-in user.

These methods will return true or false.

## Middleware

You can use the middleware to protect your routes:

```php
    Route::get('/protected-route', function () {
        // your code
    })->middleware('has-permission:perm-name');
```

### Blade

You can use the blade directives to check if the user has a given permission:

```blade
    @if (has_permission('permission-name'))
        // your code
    @endif
```

or

```blade
    @if (has_permissions(['permission-name-1', 'permission-name-2'])
        // your code
    @endif
```

or

```blade
    @if (has_role('role-name')
        // your code
    @endif
```

With this, you can show/hide parts of your views depending on the user's permissions.

# Configs

Publishing the configs, you'll have a roles.php file in your config directory. Here you
can customize the package to your needs.

## Route Naming

You can change the route names in the config file, the entry routes has, the base name of the route
the name for the index and the name for the update actions.

By default, the routes are

### Permissions

```
Index : GET permisions.index in /permissions
Update: POST permissions.update in /permissions
```

In config you can change them in the routes.permissions entry

```
url: the url path for permissions
index: the route name for index action
update: the route name for update action
```

### Users Roles

```
Index: GET permissions.users.index in /permissions/users/{user}
Update: PUT permissions.users.update in /permissions/users/{user}
```

In config, you can change them in the routes.permissions entry

```
url: the url path for user edit roles
index: the route name for index action
update: the route name for update action
```

## Permissions

Is just an array with possible permissions (strings) that can be associated to roles.

Roles are assigned to users.

In your code, you'll check with the provided methods if the user has the
required permission to grant or deny access to an action or a view, component, route etc.

Do not delete the `manage permissions` permission, it's required to access the permissions' matrix.

### Flash Messages:

When using **WITH** InertiaJS, this package can format Laravel's Flash messages on developed frontend components.
`Success` and `Error` component messages are automatically shared with your InertiaJS pages using the configured format.

### Configuration:

To enable InertiaJS compatibility, you need to setup the following environment variables in your `.env` file:

#### Configuration Options:

- `ROLES_FLASH_ENABLED`: Enable/Disable Flash messages (default: false)
- `ROLES_FLASH_FORMAT`: Set to 'inertia' for InertiaJS format, 'standard' for traditional Laravel flash messages
- `ROLES_PERMISSION_DENIED_MESSAGE`: Custom message for permission denied errors
- `ROLES_ROLE_DENIED_MESSAGE`: Custom message for role denied errors

#### Configuration example:

`.env`

```
ROLES_FLASH_ENABLED=true
ROLES_FLASH_FORMAT=inertia
ROLES_PERMISSION_DENIED_MESSAGE="You do not have PERMISSION to access this page."
ROLES_ROLE_DENIED_MESSAGE="You do not have the required ROLE to access this page."
```
