<?php

return [

    'models' => [

        'permission' => App\Models\Permission::class,
        'role' => App\Models\Role::class,
    ],

    'table_names' => [

        'roles' => 'roles',
        'permissions' => 'permission',
        'role_has_permissions' => 'role_permission',
    ],
];
