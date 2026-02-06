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

        // Team Core (Web Guard)
        'view dashboard' => [
            'code' => 5001,
            'route' => 'team.dashboard',
        ],
        'manage team' => [
            'code' => 5002,
            'route' => 'team.profile',
        ],

        // Tasks
        'list tasks' => [
            'code' => 6001,
            'route' => 'team.tasks.index',
        ],
        'show task' => [
            'code' => 6002,
            'route' => 'team.tasks.show',
        ],
        'create task' => [
            'code' => 6003,
            'route' => 'team.tasks.store',
        ],
        'update task' => [
            'code' => 6004,
            'route' => 'team.tasks.update',
        ],
        'delete task' => [
            'code' => 6005,
            'route' => 'team.tasks.destroy',
        ],

        // Members
        'list members' => [
            'code' => 7001,
            'route' => 'team.members.index',
        ],
        'invite member' => [
            'code' => 7002,
            'route' => 'team.members.invite',
        ],
        'remove member' => [
            'code' => 7003,
            'route' => 'team.members.remove',
        ],

        // Team Roles
        'list team roles' => [
            'code' => 8001,
            'route' => 'team.roles.index',
        ],
        'create team role' => [
            'code' => 8002,
            'route' => 'team.roles.store',
        ],
        'update team role' => [
            'code' => 8003,
            'route' => 'team.roles.update',
        ],
        'delete team role' => [
            'code' => 8004,
            'route' => 'team.roles.destroy',
        ],
        'view team permissions' => [
            'code' => 8005,
            'route' => 'team.roles.permissions',
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
        'team' => [
            'permissions' => [
                'view dashboard', 'manage team',
                'list tasks', 'show task', 'create task', 'update task', 'delete task',
                'list members', 'invite member', 'remove member',
                'list team roles', 'create team role', 'update team role', 'delete team role', 'view team permissions',
            ],
            'roles' => [
                'team-admin' => [
                    'view dashboard', 'manage team',
                    'list tasks', 'show task', 'create task', 'update task', 'delete task',
                    'list members', 'invite member', 'remove member',
                    'list team roles', 'create team role', 'update team role', 'delete team role', 'view team permissions',
                ],
                'team-member' => [
                    'view dashboard',
                    'list tasks', 'show task', 'create task', 'update task',
                ],
            ],
        ],
    ],
];
