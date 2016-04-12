<?php

namespace frontend\controllers;

use common\models\Group;
use common\models\GroupUser;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class GroupController extends Controller
{
    public $layout = 'main.twig';

    public $roles = [
        User::ROLE_MASTER => 'Master',
        User::ROLE_TESTER => 'Tester',
        User::ROLE_VIEWER => 'Viewer',
    ];

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'show', 'new', 'create', 'add-user', 'delete-user', 'change-user-role', 'edit', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionNew()
    {
        $model = new Group();
        return $this->render('new.twig', ['model' => $model]);
    }

    /**
     * @return string|yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Group();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->addGroup()) {
                Yii::$app->session->setFlash('success', 'Group successfully created.');
                return $this->redirect(Url::to(['group/index']));
            }
        }
        return $this->render('new.twig', compact('model'));
    }

    /**
     * @param $code
     * @return string
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    public function actionEdit($code)
    {
        // TODO: needs huge refactoring
        $group = Group::findByCode($code);

        if (Yii::$app->user->can('editGroup', ['group' => $group])) {
            if (Yii::$app->request->isPatch) {
                if (Yii::$app->request->post('type') == 'owner') {
                    return $this->changeGroupOwner($group);
                } else {
                    return $this->changeGroupDescription($group);
                }
            }

            $isOwner = Yii::$app->user->can('ownGroup', ['group' => $group]);

            $users = User::findAll(['status' => User::STATUS_ACTIVE]);

            return $this->render('edit.twig', compact('group', 'users', 'isOwner'));
        }

        throw new yii\web\ForbiddenHttpException('Permission denied');
    }

    /**
     * @param Group $group
     * @return array
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    protected function changeGroupOwner(Group $group)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->user->can('ownGroup', ['group' => $group])) {
            throw new yii\web\ForbiddenHttpException();
        }

        $group->user_id = Yii::$app->request->post('user');

        if ($group->save()) {
            return [
                'needRedirect' => !Yii::$app->user->can('ownGroup', ['group' => $group]),
            ];
        }

        throw new yii\web\ServerErrorHttpException();
    }

    /**
     * @param Group $group
     * @return yii\web\Response
     */
    protected function changeGroupDescription(Group $group)
    {
        $group->load(Yii::$app->request->post());
        $group->save();
        return $this->redirect(Url::to(['group/show', 'code' => $group->code]));
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $groups = Group::getGroups();
        return $this->render('index.twig', compact('groups'));
    }

    /**
     * @param $code
     * @return array
     * @throws \Exception
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionDelete($code)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        $group = Group::findByCode($code);

        if (!Yii::$app->user->can('ownGroup', ['group' => $group])) {
            throw new yii\web\ForbiddenHttpException();
        }

        if ($group->projects_count > 0) {
            throw new yii\web\BadRequestHttpException();
        }

        return ['success' => $group->delete()];
    }

    /**
     * @param $code
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionShow($code)
    {
        $group = Group::findByCode($code);

        if (!empty($group->description)) {
            $parseDown = new \Parsedown();
            $group->description = $parseDown->parse($group->description);
        }

        $canEdit = Yii::$app->user->can('editGroup', ['group' => $group]);

        if ($group && Yii::$app->user->can('viewGroup', ['group' => $group])) {
            $users = [];
            $groupUsers = [];

            if ($canEdit) {
                $users = ArrayHelper::index(User::getAll(), 'id');

                if (isset($users[$group->user_id])) {
                    $owner = $users[$group->user_id];
                    unset($users[$group->user_id]);
                }

                $groupUsers = $this->populateGroupWithUsers($group);
            }

            return $this->render('show.twig', array_merge(compact(
                'group',
                'users',
                'groupUsers',
                'owner'),
                [
                    'roles' => $this->roles,
                    'canEdit' => $canEdit
                ]
            ));
        }

        throw new NotFoundHttpException('Group not found');
    }

    /**
     * Builds members info for selected group (username and role)
     * @param Group $group
     * @return array
     */
    protected function populateGroupWithUsers(Group $group)
    {
        $roles = [];
        $users = [];

        if (!empty($group->groupUsers)) {
            foreach ($group->groupUsers as $role) {
                $roles[$role->user_id] = $role->role_id;
            }

            foreach ($group->users as $user) {
                $users[] = [
                    'id' => $user->id,
                    'name' => $user->username,
                    'role' => $roles[$user->id],
                ];
            }
        }

        return $users;
    }

    /**
     * Adds user to a group via ajax
     * Users in group must be unique
     * @param int $group
     * @return array
     * @throws \HttpInvalidParamException
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    public function actionAddUser($group)
    {
        if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
            throw new yii\web\BadRequestHttpException();
        }

        $group = Group::findOne($group);

        if (!Yii::$app->user->can('editGroup', ['group' => $group])) {
            throw new yii\web\ForbiddenHttpException();
        }

        $userId = Yii::$app->request->post('group_user');
        $role = Yii::$app->request->post('group_user_role');

        if ($group->isGroupOwner($userId)) {
            throw new yii\web\BadRequestHttpException('User is already a group owner');
        }

        $user = User::findOne($userId);

        if (!isset($this->roles[$role]) || !$user) {
            throw new yii\base\InvalidParamException('User or role does not exist');
        }

        if (GroupUser::findOne(['user_id' => $userId, 'group_id' => $group->id])) {
            throw new yii\web\BadRequestHttpException('User is already in group');
        }

        $userGroup = new GroupUser();
        $userGroup->group_id = $group->id;
        $userGroup->user_id = $userId;
        $userGroup->role_id = $role;
        if (!$userGroup->save()) {
            throw new yii\web\ServerErrorHttpException('Failed to save user');
        }

        return $this->renderAjax('user.twig', [
            'user' => [
                'id' => $user->id,
                'name' => $user->username,
                'role' => $role,
            ],
            'group' => $group->id,
            'roles' => $this->roles
        ]);
    }

    /**
     * Deletes user from group if he is not owner
     * @param $group
     * @return array
     * @throws \Exception
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    public function actionDeleteUser($group)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax || !Yii::$app->request->isDelete) {
            throw new yii\web\BadRequestHttpException();
        }

        $group = Group::findOne($group);

        if (!Yii::$app->user->can('editGroup', ['group' => $group])) {
            throw new yii\web\ForbiddenHttpException();
        }

        $groupUser = GroupUser::findOne([
            'user_id' => Yii::$app->request->post('user'),
            'group_id' => $group->id,
        ]);

        if (!$groupUser) {
            throw new yii\web\BadRequestHttpException();
        }

        if (!$groupUser->delete()) {
            throw new yii\web\ServerErrorHttpException();
        }

        return ['success' => true];
    }

    /**
     * @param $group
     * @return bool
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionChangeUserRole($group)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax || !Yii::$app->request->isPatch) {
            throw new yii\web\BadRequestHttpException();
        }

        if (!Yii::$app->user->can('editGroup', ['groupId' => $group])) {
            throw new yii\web\ForbiddenHttpException();
        }

        $userId = Yii::$app->request->post('user');
        $role = Yii::$app->request->post('role');

        $groupUser = GroupUser::findOne(['group_id' => $group, 'user_id' => $userId]);

        if (!$groupUser || !isset($this->roles[$role])) {
            throw new yii\base\InvalidParamException('User or role does not exist');
        }

        if ($groupUser->role_id == $role) {
            return true;
        }

        $groupUser->role_id = $role;

        return $groupUser->save();
    }
}
