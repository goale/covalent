<?php

/* @var $this yii\web\View */
/* @var $projects array */

$this->title = 'Groups';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="groups-container">
    <div class="row">
        <div class="col-md-9">
            <h2>Groups</h2>
        </div>
        <div class="col-md-3 new-group">
            <a class="btn btn-success" href="/groups/new">New group</a>
        </div>
    </div>
    <?php if (empty($groups)): ?>
        <p>No groups</p>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
            <div class="group-item">
                <a href="/groups/<?= $group['code']?>">
                    <?= $group['name'] ?>
                </a><br>
                <span class="group-item__info"><?= $group['users'] ?> users, <?= $group['projects'] ?> projects</span>
            </div>
        <?php endforeach ?>
    <?php endif ?>
</div>
