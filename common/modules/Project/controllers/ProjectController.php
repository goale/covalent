<?php

namespace common\modules\Project\controllers;


use common\modules\Project\models\Project;
use Yii;
use yii\helpers\Url;
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
    
    public function actionShow($user, $project)
    {
        return $this->render('project');
    }

    public function actionNew()
    {
        $model = new Project();

        return $this->render('new', ['model' => $model]);
    }
    
    public function actionCreate()
    {
        $model = new Project();
        if ($model->load(\Yii::$app->request->post())) {
            if ($model->add()) {
                Yii::$app->session->setFlash('success', 'Project successfully created.');
                return $this->redirect(Url::to(['project/index']));
            }
        }

        return $this->render('new', [
            'model' => $model,
        ]);
    }
}