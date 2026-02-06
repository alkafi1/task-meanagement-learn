<?php

return [
    'codes' => [
        // Users
        'list users' => [
            'code' => 1001,
            'route' => 'super-admin.users.index',
        ],
        'show user' => [
            'code' => 1002,
            'route' => 'super-admin.users.show',
        ],
        'create user' => [
            'code' => 1003,
            'route' => 'super-admin.users.store',
        ],
        'update user' => [
            'code' => 1004,
            'route' => 'super-admin.users.update',
        ],
        'delete user' => [
            'code' => 1005,
            'route' => 'super-admin.users.destroy',
        ],

        // Teams
        'list teams' => [
            'code' => 2001,
            'route' => 'super-admin.teams.index',
        ],
        'show team' => [
            'code' => 2002,
            'route' => 'super-admin.teams.show',
        ],
        'create team' => [
            'code' => 2003,
            'route' => 'super-admin.teams.store',
        ],
        'update team' => [
            'code' => 2004,
            'route' => 'super-admin.teams.update',
        ],
        'delete team' => [
            'code' => 2005,
            'route' => 'super-admin.teams.destroy',
        ],

        // Role Management
        'list roles' => [
            'code' => 3001,
            'route' => 'super-admin.roles.index',
        ],
        'show role' => [
            'code' => 3002,
            'route' => 'super-admin.roles.show',
        ],
        'create role' => [
            'code' => 3003,
            'route' => 'super-admin.roles.store',
        ],
        'update role' => [
            'code' => 3004,
            'route' => 'super-admin.roles.update',
        ],
        'delete role' => [
            'code' => 3005,
            'route' => 'super-admin.roles.destroy',
        ],

        // Permission Management
        'view permissions' => [
            'code' => 4001,
            'route' => 'super-admin.permissions.index',
        ],
    ],

    'guards' => [
        'super_admin' => [
            'permissions' => [
                'list users', 'show user', 'create user', 'update user', 'delete user',
                'list teams', 'show team', 'create team', 'update team', 'delete team',
                'list roles', 'show role', 'create role', 'update role', 'delete role',
                'view permissions',
            ],
            'roles' => [
                'super-admin' => [
                    'list users', 'show user', 'create user', 'update user', 'delete user',
                    'list teams', 'show team', 'create team', 'update team', 'delete team',
                    'list roles', 'show role', 'create role', 'update role', 'delete role',
                    'view permissions',
                ],
                'admin' => [
                    'list users', 'show user', 'update user',
                    'list teams', 'show team', 'update team',
                ],
            ],
        ],
    ],
];
