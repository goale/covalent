<?php

namespace common\modules\Project;

use yii\base\Application;
use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public $controllerNamespace = 'common\modules\Project\controllers';

    public $urlRules = [
        'GET projects' => 'project/project/explore',
        'POST projects' => 'project/project/create',
        'projects/new' => 'project/project/new',
//        [
//            'class' => 'common\modules\Project\components\ProjectUrlRule'
//        ],

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
