<?php
return [
    'viewGroup' => [
        'type' => 2,
        'description' => 'View group',
    ],
    'editGroup' => [
        'type' => 2,
        'description' => 'Edit group',
    ],
    'ownGroup' => [
        'type' => 2,
        'description' => 'Own group',
    ],
    'createGroup' => [
        'type' => 2,
        'description' => 'Create group',
    ],
    'isAdmin' => [
        'type' => 2,
        'description' => 'Is Admin',
    ],
    'user' => [
        'type' => 1,
        'ruleName' => 'projectRole',
        'children' => [
            'createGroup',
        ],
    ],
    'viewer' => [
        'type' => 1,
        'ruleName' => 'projectRole',
        'children' => [
            'user',
            'viewGroup',
        ],
    ],
    'tester' => [
        'type' => 1,
        'ruleName' => 'projectRole',
        'children' => [
            'viewer',
        ],
    ],
    'master' => [
        'type' => 1,
        'ruleName' => 'projectRole',
        'children' => [
            'tester',
            'editGroup',
        ],
    ],
    'owner' => [
        'type' => 1,
        'ruleName' => 'projectRole',
        'children' => [
            'master',
            'ownGroup',
        ],
    ],
    'admin' => [
        'type' => 1,
        'ruleName' => 'projectRole',
        'children' => [
            'master',
            'isAdmin',
        ],
    ],
];
