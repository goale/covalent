<?php

use \yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $projects array */

$this->title = 'Groups';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-index">
    <h2>Groups</h2>
    <?php if (empty($groups)): ?>
        <p>No groups</p>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
            <div class="project">
                <a href="<?php Url::to(['project/view', 'id' => (int) $group['id']]) ?>">
                    <?= $group['name'] ?>
                </a>
            </div>
        <?php endforeach ?>
    <?php endif ?>
</div>
