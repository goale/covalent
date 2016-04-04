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
    'createGroup' => [
        'type' => 2,
        'description' => 'Create group',
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
    'admin' => [
        'type' => 1,
        'ruleName' => 'projectRole',
        'children' => [
            'master',
        ],
    ],
];
