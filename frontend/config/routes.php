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
    'enableStrictParsing' => true,

    'rules' => [
        [
            'class' => 'frontend\components\ProjectUrlRule',
        ],
    ],
];