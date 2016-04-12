<?php

namespace console\controllers;

use common\components\rbac\ProjectRule;
use yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Clear old roles before initialization
        $auth->removeAll();

        // Add our project rule
        $rule = new ProjectRule();
        $auth->add($rule);

        // View group permission
        $viewGroup = $auth->createPermission('viewGroup');
        $viewGroup->description = 'View group';
        $auth->add($viewGroup);

        // Edit group permission
        $editGroup = $auth->createPermission('editGroup');
        $editGroup->description = 'Edit group';
        $auth->add($editGroup);

        // Own group
        $ownGroup = $auth->createPermission('ownGroup');
        $ownGroup->description = 'Own group';
        $auth->add($ownGroup);

        // Create group permission
        $createGroup = $auth->createPermission('createGroup');
        $createGroup->description = 'Create group';
        $auth->add($createGroup);

        // Administrator permissions
        $doAll = $auth->createPermission('doAll');
        $doAll->description = 'Do all what you want';
        $auth->add($doAll);


        // TODO: project permissions

        // Create user role
        $user = $auth->createRole('user');
        $user->ruleName = $rule->name;
        $auth->add($user);
        $auth->addChild($user, $createGroup);

        // Create viewer role
        $viewer = $auth->createRole('viewer');
        $viewer->ruleName = $rule->name;
        $auth->add($viewer);
        $auth->addChild($viewer, $user);
        $auth->addChild($viewer, $viewGroup);

        // Create tester role
        $tester = $auth->createRole('tester');
        $tester->ruleName = $rule->name;
        $auth->add($tester);
        $auth->addChild($tester, $viewer);

        // Create master role
        $master = $auth->createRole('master');
        $master->ruleName = $rule->name;
        $auth->add($master);
        $auth->addChild($master, $tester);
        $auth->addChild($master, $editGroup);

        // Create owner role
        $owner = $auth->createRole('owner');
        $owner->ruleName = $rule->name;
        $auth->add($owner);
        $auth->addChild($owner, $master);
        $auth->addChild($owner, $ownGroup);

        // Create admin role
        $admin = $auth->createRole('admin');
        $admin->ruleName = $rule->name;
        $auth->add($admin);
        $auth->addChild($admin, $owner);
        $auth->addChild($admin, $doAll);
    }
}