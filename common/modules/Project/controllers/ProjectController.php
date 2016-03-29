<?php

namespace common\commands\Project\controllers;


use common\modules\Project\models\Group;
use common\modules\Project\models\Project;
use yii;
use yii\web\Controller;

class ProjectController extends Controller
{
    public function actionIndex()
    {
        $projects = Project::getAll();
        
        return $this->render('index', ['projects' => $projects]);
    }

    public function actionExplore()
    {
        $projects = Project::getPublic();

        return $this->render('index', ['projects' => $projects]);
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
            // TODO: check rights
            if ($group = Group::findOne($groupId)) {
                $model->group_id = $group['id'];
                $namespace = $group->code;
                $storeInGroup = true;
            }
        }

        return $this->render('new', [
            'model' => $model,
            'namespace' => $namespace,
            'storeInGroup' => $storeInGroup,
        ]);
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