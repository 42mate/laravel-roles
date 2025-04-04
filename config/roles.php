<?php

return [
    "routes" => [
        "permissions" => [
            "url" => "permissions",
            "index" => "permissions.index",
            "update" => "permissions.update",
        ],
        "users" => [
            "url" => "permissions/users/{user}",
            "index" => "permissions.users.index",
            "update" => "permissions.users.update",
        ],
    ],
    "permissions" => ["manage users roles", "manage permissions"],
    "tables_names" => [
        "roles" => "roles",
        "role_permissions" => "role_permissions",
        "role_user" => "role_user",
        "users" => "users",
    ],

    "redirects" => [
        "roles" => [
            "default" => "index",
        ],
        "permissions" => [
            "default" => "index",
        ],
    ],

    "superadmin" => "admin",
];
