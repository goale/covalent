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

        $groupPermissions = $this->createGroupPermissions($auth);

        // Administrator permissions
        $doAll = $auth->createPermission('doAll');
        $doAll->description = 'Do all what you want';
        $auth->add($doAll);

        // TODO: project permissions
        $projectPermissions = $this->createProjectPermissions($auth);

        // Create user role
        $user = $auth->createRole('user');
        $user->ruleName = $rule->name;
        $auth->add($user);

        // Create viewer role
        $viewer = $auth->createRole('viewer');
        $viewer->ruleName = $rule->name;
        $auth->add($viewer);
        $auth->addChild($viewer, $user);
        $auth->addChild($viewer, $groupPermissions['view']);
        $auth->addChild($viewer, $projectPermissions['view']);

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
        $auth->addChild($master, $groupPermissions['edit']);
        $auth->addChild($master, $projectPermissions['edit']);

        // Create owner role
        $owner = $auth->createRole('owner');
        $owner->ruleName = $rule->name;
        $auth->add($owner);
        $auth->addChild($owner, $master);
        $auth->addChild($owner, $groupPermissions['own']);
        $auth->addChild($owner, $projectPermissions['own']);

        // Create admin role
        $admin = $auth->createRole('admin');
        $admin->ruleName = $rule->name;
        $auth->add($admin);
        $auth->addChild($admin, $owner);
        $auth->addChild($admin, $doAll);
    }

    /**
     * @param yii\rbac\ManagerInterface $auth
     * @return array
     */
    private function createGroupPermissions(yii\rbac\ManagerInterface $auth)
    {
        $view = $auth->createPermission('viewGroup');
        $view->description = 'View group';
        $auth->add($view);

        $edit = $auth->createPermission('editGroup');
        $edit->description = 'Edit group';
        $auth->add($edit);

        $own = $auth->createPermission('ownGroup');
        $own->description = 'Own group';
        $auth->add($own);

        return compact('view', 'edit', 'own');
    }

    /**
     * @param yii\rbac\ManagerInterface $auth
     * @return array
     */
    private function createProjectPermissions(yii\rbac\ManagerInterface $auth)
    {
        $view = $auth->createPermission('viewProject');
        $view->description = 'View project';
        $auth->add($view);

        $edit = $auth->createPermission('editProject');
        $edit->description = 'Edit project';
        $auth->add($edit);

        $own = $auth->createPermission('ownProject');
        $own->description = 'Own project';
        $auth->add($own);

        return compact('view', 'edit', 'own');
    }
}