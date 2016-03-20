<?php
/* @var $this yii\web\View */
/** @var Group $group */

use common\modules\Project\models\Group;
use yii\helpers\Html;

$this->title = $group['name'];
?>
<h1><?= Html::encode($this->title) ?></h1>
<div class="group-description">
    <?= Html::encode($group['description']) ?>
</div>
