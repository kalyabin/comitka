<?php

use VcsCommon\BaseBranch;
use VcsCommon\BaseCommit;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use project\models\Project;

/* @var $this View */
/* @var $branches BaseBranch[] */
/* @var $project Project */
?>

<h4><?=Yii::t('project', 'Branches')?></h4>
<div class="list-group">
    <?php foreach ($branches as $branch):?>
        <?php
        /* @var $branch BaseBranch */
        /* @var $commit BaseCommit */
        $commit = $branch->getHeadCommit();
        ?>
        <div class="list-group-item col-md-12">
            <div class="col-md-1">
                <h5 class="list-group-item-heading"><?=Html::encode($branch->getId())?></h5>
            </div>
            <div class="col-md-2">
                <span class="commit-date"><?=$commit->getDate()->format('d\'M y H:i:s')?></span>
            </div>
            <div class="col-md-9">
                <a href="<?=Url::to(['commit-summary', 'id' => $project->id, 'commitId' => $commit->getId()])?>">
                    <strong class="list-group-item-heading"><?=Html::encode($commit->message)?></strong>
                </a>
            </div>
        </div>
    <?php endforeach;?>
</div>
