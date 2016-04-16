<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    //'enableStrictParsing' => true,
    'rules' => [
        'GET projects/new' => 'project/new',
        'GET projects' => 'project/explore',
        'POST projects' => 'project/create',
        'POST groups/<code>/users' => 'group/add-user',
        'PATCH groups/<code>/users' => 'group/change-user-role',
        'DELETE groups/<code>/users' => 'group/delete-user',
        'groups/<code>/delete' => 'group/delete',
        'groups/<code>/edit' => 'group/edit',
        'GET groups/new' => 'group/new',
        'groups/<code>' => 'group/show',
        'GET groups' => 'group/index',
        'POST groups' => 'group/create',
        'GET users' => 'user/index',
        [
            'class' => 'common\components\ProjectUrlRule',
        ]
    ],
];