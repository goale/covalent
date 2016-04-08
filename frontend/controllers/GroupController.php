<?php

namespace frontend\controllers;

use common\models\Group;
use common\models\GroupUser;
use common\models\User;
use yii;
use yii\filters\AccessControl;
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

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'show', 'new', 'create', 'add-user', 'delete-user'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionNew()
    {
        $model = new Group();
        return $this->render('new.twig', ['model' => $model]);
    }

    public function actionCreate()
    {
        $model = new Group();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->addGroup()) {
                Yii::$app->session->setFlash('success', 'Group successfully created.');
                return $this->redirect(Url::to(['group/index']));
            }
        }
        return $this->render('new.twig', ['model' => $model]);
    }

    public function actionIndex()
    {
        return $this->render('index', ['groups' => Group::getGroups()]);
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

        if ($group && Yii::$app->user->can('viewGroup', ['groupId' => $group->id])) {
            $roles = [];
            $users = [];
            $groupUsers = [];

            foreach ($group->groupUsers as $role) {
                $roles[$role->user_id] = $role->role_id;
            }

            foreach ($group->users as $user) {
                $groupUsers[] = [
                    'id' => $user->id,
                    'name' => $user->username,
                    'role' => $roles[$user->id],
                ];
            }

            if (Yii::$app->user->can('editGroup', ['groupId' => $group->id])) {
                $users = User::findWithoutOwner($group->id);
            }

            return $this->render('show.twig', [
                'group' => $group,
                'users' => $users,
                'groupUsers' => $groupUsers,
                'owner' => User::findOne($group->user_id),
                'roles' => $this->roles,
                'canEdit' => Yii::$app->user->can('editGroup', ['groupId' => $group->id]),
            ]);
        }

        throw new NotFoundHttpException('Group not found');
    }

    /**
     * Adds user to a group via ajax
     * Users in group must be unique
     * @param $group
     * @return array
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     * @throws yii\base\InvalidParamException
     */
    public function actionAddUser($group)
    {
        if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
            throw new yii\web\BadRequestHttpException();
        }

        if (!Yii::$app->user->can('editGroup', ['groupId' => $group])) {
            throw new yii\web\ForbiddenHttpException();
        }

        $userId = Yii::$app->request->post('group_user');
        $role = Yii::$app->request->post('group_user_role');

        $user = User::findOne($userId);

        if (Group::findOne($group)->isGroupOwner($userId)) {
            throw new yii\web\BadRequestHttpException('User is already a group owner');
        }

        if (!isset($this->roles[$role]) || !$user) {
            throw new yii\base\InvalidParamException('User or role does not exist');
        }

        if (GroupUser::findOne(['user_id' => $userId, 'group_id' => $group])) {
            throw new yii\base\InvalidParamException('User is already in group');
        }

        $userGroup = new GroupUser();
        $userGroup->group_id = $group;
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
            'group' => $group,
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

        if (!Yii::$app->user->can('editGroup', ['groupId' => $group])) {
            throw new yii\web\ForbiddenHttpException();
        }

        $groupUser = GroupUser::findOne([
            'user_id' => Yii::$app->request->post('user'),
            'group_id' => $group,
        ]);

        if (!$groupUser) {
            throw new yii\web\BadRequestHttpException();
        }

        if (!$groupUser->delete()) {
            throw new yii\web\ServerErrorHttpException();
        }

        return ['success' => true];
    }

}
