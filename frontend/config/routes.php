<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    //'enableStrictParsing' => true,
    'rules' => [
        'POST groups/<group>/users' => 'group/add-user',
        'PATCH groups/<group>/users' => 'group/change-user-role',
        'DELETE groups/<group>/users' => 'group/delete-user',
        'groups/<code>/delete' => 'group/delete',
        'groups/<code>/edit' => 'group/edit',
        'GET groups/new' => 'group/new',
        'groups/<code>' => 'group/show',
        'GET groups' => 'group/index',
        'POST groups' => 'group/create',
        'GET users' => 'user/index',
    ],
];