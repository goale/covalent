<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    //'enableStrictParsing' => true,
    'rules' => [
        'GET groups' => 'group/index',
        'POST groups' => 'group/create',
        'groups/new' => 'group/new',
        'groups/<code>' => 'group/show',
    ],
];