<?php

namespace common\components\rbac;


use common\models\Group;
use common\models\GroupUser;
use common\models\Project;
use common\models\ProjectUser;
use common\models\User;
use yii;
use yii\rbac\Item;
use yii\rbac\Rule;

class ProjectRule extends Rule
{

    public $name = 'projectRule';

    /**
     * Executes the rule.
     *
     * @param string|integer $user the user ID. This should be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to [[ManagerInterface::checkAccess()]].
     * @return boolean a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $role = Yii::$app->user->identity->role;
        } else if (isset($params['project'])) {
            $role = $this->getUserProjectRole($params['project']);
        } else if (isset($params['group'])) {
            $role = $this->getUserGroupRole($params['group']);
        } else {
            $role = Yii::$app->user->identity->role;
        }

        switch ($item->name) {
            case 'admin':
                return $role >= User::ROLE_ADMIN;
            case 'owner':
                return $role >= User::ROLE_OWNER;
            case 'master':
                return $role >= User::ROLE_MASTER;
            case 'tester':
                return $role >= User::ROLE_TESTER;
            case 'viewer':
                return $role >= User::ROLE_VIEWER;
            default:
                return $role == User::ROLE_USER;
        }
    }

    /**
     * @param Project $project
     * @return int user role
     */
    protected function getUserProjectRole($project)
    {
        $userId = Yii::$app->user->id;

        if ($project->isProjectOwner($userId)) {
            return User::ROLE_OWNER;
        }

        if ($project->group_id > 0) {
            $group = Group::findOne($project->group_id);
            return $this->getUserGroupRole($group);
        }

        if ($projectRole = ProjectUser::findOne(['user_id' => $userId, 'project_id' => $project->id])) {
            return $projectRole->role;
        }

        return User::ROLE_USER;
    }

    /**
     * Gets user role in group
     * @param Group $group
     * @return int
     */
    protected function getUserGroupRole($group)
    {
        $userId = Yii::$app->user->id;

        if ($group->isGroupOwner($userId)) {
            return User::ROLE_OWNER;
        }

        if ($groupRole = GroupUser::findOne(['user_id' => $userId, 'group_id' => $group->id])) {
            return $groupRole->role;
        }

        return User::ROLE_USER;
    }
}