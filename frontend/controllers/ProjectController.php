<?php

namespace frontend\controllers;


use common\models\Group;
use common\models\Project;
use yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;

class ProjectController extends Controller
{
    const PROJECTS_PER_PAGE = 20;

    public $layout = 'main.twig';

    public function actionIndex()
    {
        $projects = Project::getAll();
        
        return $this->render('index', ['projects' => $projects]);
    }

    public function actionExplore()
    {
        $projects = Project::getPublic();

        $projectsProvider = new ActiveDataProvider([
            'query' => $projects,
            'pagination' => [
                'pageSize' => self::PROJECTS_PER_PAGE,
            ],
        ]);

        return $this->render('index.twig', [
            'projects' => $projectsProvider->models,
            'pagination' => $projectsProvider->pagination,
        ]);
    }
    
    public function actionShow($project)
    {
        return $this->render('project');
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

        return $this->render('new', [
            'model' => $model,
        ]);
    }
}