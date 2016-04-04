<?php

namespace frontend\controllers;

use common\models\Group;
use yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class GroupController extends Controller
{
    public $layout = 'main.twig';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'show', 'new', 'create'],
                        'roles' => ['@'],
                    ]

                ]
            ],
        ];
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
        return $this->render('index', ['groups' => Group::getGroups()]);
    }

    public function actionShow($code)
    {
        $group = Group::findByCode($code);

        if ($group && Yii::$app->user->can('viewGroup', ['groupId' => $group->id])) {
            return $this->render('show', ['group' => $group]);
        }

        throw new NotFoundHttpException('Group not found');
    }

}
