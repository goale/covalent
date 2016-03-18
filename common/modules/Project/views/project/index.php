<?php

use \yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $projects array */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-index">
    <h2>Projects</h2>
    <?php if (empty($projects)): ?>
        <p>No projects</p>
    <?php else: ?>
        <?php foreach ($projects as $project): ?>
            <div class="project">
                <a href="<?php Url::to(['project/view', 'id' => (int) $project['id']]) ?>">
                    <?= $project['name'] ?>
                </a>
            </div>
        <?php endforeach ?>
    <?php endif ?>
</div>
