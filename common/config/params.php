<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    'modules' => [
        'project' => [
            'class' => 'common\modules\Project\Module',
        ],
    ],
];
