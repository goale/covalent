<?php
/**
 * Created by PhpStorm.
 * User: agoncharov
 * Date: 15.03.16
 * Time: 15:34
 */

namespace frontend\controllers;


use common\models\Project;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;

class ProjectController extends Controller
{
    public function actionIndex()
    {
        $projects = Project::find()
            ->select(['id', 'name', 'code'])
            ->where(['active' => Project::STATUS_ACTIVE])
            ->asArray()
            ->all();
        
        return $this->render('index', ['projects' => $projects]);
    }
    
    public function actionView($id)
    {
        return $this->render('project', ['projectId' => $id]);
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