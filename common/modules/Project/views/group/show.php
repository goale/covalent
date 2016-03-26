<?php
/* @var $this yii\web\View */
/** @var Group $group */

use common\modules\Project\models\Group;
use yii\bootstrap\Nav;
use yii\helpers\Html;

$this->title = $group['name'];
?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small>@<?= Html::encode($group['code']) ?></small></h1>
</div>
<div class="group-description">
    <div class="well well-lg">
        <?= Html::encode($group['description']) ?>
    </div>
    <div class="group-description__projects">
        <?php
        $navItems = [
            ['label' => 'Projects', 'url' => ['#'], 'class' => 'projects'],
        ];
        echo Nav::widget([
            'options' => ['class' => 'nav nav-tabs'],
            'items' => $navItems
        ]);
        ?>
        <a
            href="/projects/new?group=<?= $group['id'] ?>"
            class="btn btn-success group-description__projects-btn">
            <?= Yii::t('app', 'Add project') ?>
        </a>
        <?php foreach ($group->projects as $project): ?>
            <div class="project">
                <a href="<?= $project['slug'] ?>">
                    <?= $project['name'] ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
