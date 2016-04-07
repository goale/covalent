<?php

namespace common\components\rbac;


use common\models\Group;
use common\models\GroupUser;
use common\models\User;
use common\modules\Project\models\Project;
use yii;
use yii\rbac\Item;
use yii\rbac\Rule;

class ProjectRule extends Rule
{

    public $name = 'projectRole';

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
            $role = $this->getUserRole($params['project']);
        } else if (isset($params['groupId'])) {
            $role = $this->getUserGroupRole($params['groupId']);
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
    protected function getUserRole($project)
    {
        if ($project->group_id > 0) {
            return $this->getUserGroupRole($project->group_id);
        }

        return $this->getUserProjectRole($project);
    }

    /**
     * Gets user role in group
     * @param int $groupId
     * @return int
     */
    protected function getUserGroupRole($groupId)
    {
        $group = Group::findOne($groupId);
        $userId = Yii::$app->user->id;

        if ($group->isGroupOwner($userId)) {
            return User::ROLE_OWNER;
        }

        if ($groupRole = GroupUser::findOne(['user_id' => $userId, 'group_id' => $groupId])) {
            return $groupRole->role_id;
        }

        return User::ROLE_USER;
    }

    /**
     * @param Project $project
     * @return int
     */
    private function getUserProjectRole($project)
    {
        // TODO: implement project roles (owner, project->user relations, public projects, etc.)
        if ($project->user_id == Yii::$app->user->id) {
            return User::ROLE_MASTER;
        }

        return User::ROLE_USER;
    }
}