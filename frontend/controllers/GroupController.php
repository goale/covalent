<?php

namespace frontend\controllers;

use common\models\Group;
use common\models\GroupUser;
use common\models\Project;
use common\models\User;
use common\traits\MemberTrait;
use common\traits\StringyTrait;
use yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class GroupController extends Controller
{
    use MemberTrait, StringyTrait;
    
    public $layout = 'main.twig';

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
                        'actions' => ['add-user', 'create'],
                        'verbs' => ['POST'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['change-user-role'],
                        'verbs' => ['PATCH'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete-user', 'delete'],
                        'verbs' => ['DELETE'],
                    ],
                    [
                        'allow' => true,
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

        $userId = Yii::$app->request->post('user');

        if ($groupUser = GroupUser::findOne(['user_id' => $userId, 'group_id' => $group->id])) {
            $groupUser->delete();
        }

        $group->user_id = $userId;

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

        $group->description = $this->markdownify($group->description);

        $canEdit = Yii::$app->user->can('editGroup', ['group' => $group]);

        if ($group && Yii::$app->user->can('viewGroup', ['group' => $group])) {
            $projects = Project::findAll(['group_id' => $group->id]);

            if ($canEdit) {
                $users = ArrayHelper::index(User::getAll(), 'id');

                if (isset($users[$group->user_id])) {
                    $owner = $users[$group->user_id];
                    unset($users[$group->user_id]);
                }

                $groupUsers = $this->buildMembersWithRoles($users, $group->groupUsers);
            }

            return $this->render('show.twig', array_merge(compact(
                'projects',
                'group',
                'users',
                'groupUsers',
                'owner'),
                [
                    'roles' => User::$roles,
                    'canEdit' => $canEdit
                ]
            ));
        }

        throw new NotFoundHttpException('Group not found');
    }

    /**
     * Adds user to a group via ajax
     * Users in group must be unique
     * @param string $code
     * @return array
     * @throws \HttpInvalidParamException
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    public function actionAddUser($code)
    {
        if (!Yii::$app->request->isAjax) {
            throw new yii\web\BadRequestHttpException();
        }

        $group = Group::findByCode($code);

        if (!Yii::$app->user->can('editGroup', ['group' => $group])) {
            throw new yii\web\ForbiddenHttpException();
        }

        $userId = Yii::$app->request->post('user');
        $role = Yii::$app->request->post('role');

        if ($group->isGroupOwner($userId)) {
            throw new yii\web\BadRequestHttpException('User is already a group owner');
        }

        $user = User::findOne($userId);

        if (!isset(User::$roles[$role]) || !$user) {
            throw new yii\base\InvalidParamException('User or role does not exist');
        }

        if (GroupUser::findOne(['user_id' => $userId, 'group_id' => $group->id])) {
            throw new yii\web\BadRequestHttpException('User is already in group');
        }

        $userGroup = new GroupUser();
        $userGroup->group_id = $group->id;
        $userGroup->user_id = $userId;
        $userGroup->role = $role;
        if (!$userGroup->save()) {
            throw new yii\web\ServerErrorHttpException('Failed to save user');
        }

        return $this->renderAjax('@views/partials/user.twig', [
            'user' => [
                'id' => $user->id,
                'name' => $user->username,
                'role' => $role,
            ],
            'roles' => User::$roles
        ]);
    }

    /**
     * Deletes user from group if he is not owner
     * @param $code
     * @return array
     * @throws \Exception
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    public function actionDeleteUser($code)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax) {
            throw new yii\web\BadRequestHttpException();
        }

        $group = Group::findByCode($code);

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
     * @param string $code
     * @return bool
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionChangeUserRole($code)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax) {
            throw new yii\web\BadRequestHttpException();
        }

        $group = Group::findByCode($code);

        if (!Yii::$app->user->can('editGroup', ['group' => $group])) {
            throw new yii\web\ForbiddenHttpException();
        }

        $userId = Yii::$app->request->post('user');
        $role = Yii::$app->request->post('role');

        $groupUser = GroupUser::findOne(['group_id' => $group->id, 'user_id' => $userId]);

        if (!$groupUser || !isset(User::$roles[$role])) {
            throw new yii\base\InvalidParamException('User or role does not exist');
        }

        if ($groupUser->role == $role) {
            return true;
        }

        $groupUser->role = $role;

        return $groupUser->save();
    }
}
