<?php

namespace common\modules\Project\controllers;

use common\modules\Project\models\Group;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;

class GroupController extends Controller
{
    public function actionAddGroup()
    {
        return $this->render('add-group');
    }
    
    public function actionNew()
    {
        $model = new Group();
        return $this->render('new', ['model' => $model]);
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
        return $this->render('create');
    }

    public function actionIndex()
    {
        $groups = Group::find()
            ->select(['id', 'name', 'code'])
            ->asArray()
            ->all();
        
        return $this->render('index', ['groups' => $groups]);
    }

    public function actionView($id)
    {
        return $this->render('show');
    }

}
