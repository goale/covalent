<?php

namespace frontend\controllers;

use common\models\Group;
use yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class GroupController extends Controller
{
    public $layout = 'main.twig';

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

    public function actionShow($code)
    {
        $group = Group::findByCode($code);

        if (!$group) {
            throw new NotFoundHttpException();
        }
        return $this->render('show', ['group' => $group]);
    }

}
