<?php
/**
 * Created by PhpStorm.
 * User: agoncharov
 * Date: 15.03.16
 * Time: 15:31
 */

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'project',
            'except' => ['options'],
            'extraPatterns' => [
                'GET new' => 'new'
            ]
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'group',
            'except' => ['options'],
            'extraPatterns' => [
                'GET new' => 'new'
            ]
        ]
    ]
];