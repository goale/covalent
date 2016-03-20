<?php

namespace common\modules\Project;

use yii\base\Application;
use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public $controllerNamespace = 'common\modules\Project\controllers';

    public $urlRules = [
        'projects' => 'project/project/explore',
        'GET groups' => 'project/group/index',
        'POST groups' => 'project/group/create',
        'groups/new' => 'project/group/new',
        'groups/<code>' => 'project/group/show',
        [
            'class' => 'common\modules\Project\components\ProjectUrlRule'
        ],

    ];

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules($this->urlRules, false);
    }
}
