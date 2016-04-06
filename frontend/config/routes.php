<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    //'enableStrictParsing' => true,
    'rules' => [
        'POST groups/<group>/users' => 'group/add-user',
        'DELETE groups/<group>/users' => 'group/delete-user',
        'groups/new' => 'group/new',
        'groups/<code>' => 'group/show',
        'GET groups' => 'group/index',
        'POST groups' => 'group/create',
    ],
];