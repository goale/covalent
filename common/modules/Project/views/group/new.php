<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Add group';
$this->params['breadcrumbs'] = [
    ['label' => 'Groups', 'url' => ['index']],
    $this->title];
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!empty($model->errors)): ?>
        <?php var_dump($model->errors); ?>
    <?php endif ?>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin([
                'id' => 'contact-form',
                'action' => \yii\helpers\Url::to(['group/index'])
            ]); ?>

            <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

            <div class="form-group">
                <?= Html::submitButton('Add group', ['class' => 'btn btn-primary', 'name' => 'add-group-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
