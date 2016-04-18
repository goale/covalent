<?php

namespace frontend\controllers;


use common\models\Group;
use common\models\GroupUser;
use common\models\Project;
use common\models\ProjectUser;
use common\models\User;
use common\traits\MemberTrait;
use common\traits\StringyTrait;
use yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;

class ProjectController extends Controller
{
    use MemberTrait, StringyTrait;

    const PROJECTS_PER_PAGE = 20;

    public $layout = 'main.twig';

    public function actionIndex()
    {
        $projects = Project::getAll();

        return $this->render('index', ['projects' => $projects]);
    }

    public function actionExplore()
    {
        $projects = Project::findUserProjects(Yii::$app->user->id);

        return $this->render('index.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * @param Project $project
     * @return string
     * @throws yii\web\NotFoundHttpException
     */
    public function actionShow(Project $project)
    {
        if (!Yii::$app->user->can('viewProject', compact('project'))) {
            throw new yii\web\NotFoundHttpException(Yii::t('app', 'Project not found'));
        }

        $project->description = $this->markdownify($project->description);

        $canEdit = Yii::$app->user->can('editProject', compact('project'));
        $roles = User::$roles;

        if ($canEdit) {
            $users = ArrayHelper::index(User::getAll(), 'id');

            $projectUsers = $this->buildMembersWithRoles($users, $project->projectUsers);

            if (isset($users[$project->user_id])) {
                $owner = $users[$project->user_id];
                unset($users[$project->user_id]);
            }

            if ($project->group_id) {
                $groupUsers = $this->buildMembersWithRoles(
                    $users,
                    GroupUser::findAll(['group_id' => $project->group_id])
                );
            }
        }

        return $this->render('show.twig', compact(
            'project',
            'canEdit',
            'roles',
            'users',
            'owner',
            'projectUsers',
            'groupUsers'
        ));
    }

    public function actionNew()
    {
        $model = new Project();
        $namespace = Yii::$app->user->identity->username;
        $storeInGroup = false;

        if ($groupId = Yii::$app->request->get('group')) {
            $group = Group::findOne($groupId);
            if (Yii::$app->user->can('editGroup', compact('group'))) {
                $model->group_id = $group['id'];
                $namespace = $group->code;
                $storeInGroup = true;
            }
        }

        return $this->render('new.twig', compact('model', 'namespace', 'storeInGroup'));
    }

    public function actionCreate()
    {
        $model = new Project();
        if ($model->load(\Yii::$app->request->post())) {
            if ($model->add()) {
                Yii::$app->session->setFlash('success', 'Project successfully created.');

                return $this->redirect($model->slug);
            }
        }

        return $this->render('new.twig', [
            'model' => $model,
        ]);
    }

    public function actionEdit(Project $project)
    {
        if (Yii::$app->user->can('editProject', compact('project'))) {
            if (Yii::$app->request->isPatch) {
                if (Yii::$app->request->post('type') == 'owner') {
                    $this->changeProjectOwner($project);
                } else {
                    $this->changeProjectInfo($project);
                }
            }

            $isOwner = Yii::$app->user->can('ownProject', compact('project'));

            $users = User::findAll(['status' => User::STATUS_ACTIVE]);

            return $this->render('edit.twig', compact('project', 'isOwner', 'users'));
        }

        throw new yii\web\ForbiddenHttpException();
    }

    /**
     * @param Project $project
     * @return array
     * @throws \Exception
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    private function changeProjectOwner(Project $project)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->user->can('ownProject', compact('project'))) {
            throw new yii\web\ForbiddenHttpException();
        }

        $userId = Yii::$app->request->post('user');

        if ($projectUser = ProjectUser::findOne(['user_id' => $userId, 'project_id' => $project->id])) {
            $projectUser->delete();
        }

        $project->user_id = $userId;

        if ($project->save()) {
            return [
                'needRedirect' => !Yii::$app->user->can('ownGroup', compact('project')),
            ];
        }

        throw new yii\web\ServerErrorHttpException();
    }

    /**
     * @param Project $project
     * @return yii\web\Response
     */
    private function changeProjectInfo(Project $project)
    {
        $project->load(Yii::$app->request->post());
        $project->save();
        return $this->redirect($project->slug);
    }

    public function actionDelete(Project $project)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->user->can('ownProject', compact('project'))) {
            throw new yii\web\ForbiddenHttpException();
        }

        return ['success' => $project->delete()];
    }

    /**
     * @param Project $project
     * @return string
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    public function actionAddMember(Project $project)
    {
        if (!Yii::$app->user->can('editProject', compact('project'))) {
            throw new yii\web\ForbiddenHttpException();
        }

        $userId = Yii::$app->request->post('user');
        $role = Yii::$app->request->post('role');

        if ($project->isProjectOwner($userId)) {
            throw new yii\web\BadRequestHttpException();
        }

        if (ProjectUser::findOne(['user_id' => $userId, 'project_id' => $project->id])) {
            throw new yii\web\BadRequestHttpException(Yii::t('app', 'User is already a project member'));
        }

        $projectUser = new ProjectUser();
        $projectUser->project_id = $project->id;
        $projectUser->user_id = $userId;
        $projectUser->role = $role;

        $user = User::findOne($userId);

        if (!$user || !isset(User::$roles[$role])) {
            throw new yii\web\BadRequestHttpException();
        }

        if (!$projectUser->save()) {
            throw new yii\web\ServerErrorHttpException(Yii::t('app', 'Failed to save user'));
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
     * @param $project
     * @return array
     * @throws \Exception
     * @throws yii\web\BadRequestHttpException
     * @throws yii\web\ForbiddenHttpException
     * @throws yii\web\ServerErrorHttpException
     */
    public function actionDeleteMember(Project $project)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->user->can('editProject', compact('project'))) {
            throw new yii\web\ForbiddenHttpException();
        }

        $projectUser = ProjectUser::findOne([
            'user_id' => Yii::$app->request->post('user'),
            'project_id' => $project->id]);

        if (!$projectUser) {
            throw new yii\web\BadRequestHttpException();
        }

        if ($projectUser->delete()) {
            return ['success' => true];
        }

        throw new yii\web\ServerErrorHttpException();
    }

    /**
     * @param Project $project
     * @return bool
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionChangeMemberRole(Project $project)
    {
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->user->can('editProject', compact('project'))) {
            throw new yii\web\ForbiddenHttpException();
        }

        $userId = Yii::$app->request->post('user');
        $role = Yii::$app->request->post('role');

        $projectUser = ProjectUser::findOne(['project_id' => $project->id, 'user_id' => $userId]);

        if (!$projectUser || !isset(User::$roles[$role])) {
            throw new yii\base\InvalidParamException(Yii::t('app', 'User or role does not exist'));
        }

        if ($projectUser->role == $role) {
            return true;
        }

        $projectUser->role = $role;

        return $projectUser->save();
    }
}