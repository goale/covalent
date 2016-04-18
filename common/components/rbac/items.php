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
    'doAll' => [
        'type' => 2,
        'description' => 'Do all what you want',
    ],
    'viewProject' => [
        'type' => 2,
        'description' => 'View project',
    ],
    'editProject' => [
        'type' => 2,
        'description' => 'Edit project',
    ],
    'ownProject' => [
        'type' => 2,
        'description' => 'Own project',
    ],
    'user' => [
        'type' => 1,
        'ruleName' => 'projectRule',
    ],
    'viewer' => [
        'type' => 1,
        'ruleName' => 'projectRule',
        'children' => [
            'user',
            'viewGroup',
            'viewProject',
        ],
    ],
    'tester' => [
        'type' => 1,
        'ruleName' => 'projectRule',
        'children' => [
            'viewer',
        ],
    ],
    'master' => [
        'type' => 1,
        'ruleName' => 'projectRule',
        'children' => [
            'tester',
            'editGroup',
            'editProject',
        ],
    ],
    'owner' => [
        'type' => 1,
        'ruleName' => 'projectRule',
        'children' => [
            'master',
            'ownGroup',
            'ownProject',
        ],
    ],
    'admin' => [
        'type' => 1,
        'ruleName' => 'projectRule',
        'children' => [
            'owner',
            'doAll',
        ],
    ],
];
