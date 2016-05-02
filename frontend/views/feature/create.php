<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Feature */

$this->title = 'Create Feature';
$this->params['breadcrumbs'][] = ['label' => 'Features', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feature-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
