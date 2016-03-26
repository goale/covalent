<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ContactForm */
/* @var $group \common\modules\Project\models\Group */
/* @var $storeInGroup boolean */
/* @var $namespace string */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Add project';
$this->params['breadcrumbs'] = [
    ['label' => 'Projects', 'url' => ['index']],
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
                'action' => \yii\helpers\Url::to(['project/explore'])
            ]); ?>

            <div class="form-group">
                <?= $form->field($model, 'name', [
                        'template' => "{label}\n<div class=\"input-group\"><div class=\"input-group-addon\">$namespace/</div>\n{input}</div>{error}"
                    ])->textInput(['autofocus' => true]) ?>
            </div>

            <?php if ($storeInGroup): ?>
                <?= $form->field($model, 'group_id')->hiddenInput()->label(false) ?>
            <?php else: ?>
                <?= $form->field($model, 'public')->checkbox() ?>
            <?php endif; ?>

            <?= $form->field($model, 'url') ?>

            <?= $form->field($model, 'source_url') ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

            <div class="form-group">
                <?= Html::submitButton('Add', ['class' => 'btn btn-primary', 'name' => 'add-project-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
