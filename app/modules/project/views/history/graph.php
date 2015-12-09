<?php

use project\models\Project;
use project\widgets\ProjectPanel;
use VcsCommon\BaseBranch;
use VcsCommon\BaseCommit;
use VcsCommon\Graph;
use yii\bootstrap\Html;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\LinkPager;

/* @var $this View */
/* @var $project Project */
/* @var $graph Graph[] */
/* @var $pagination Pagination */
/* @var $branches BaseBranch[] */

/**
 * @todo Render graph using RaphaelJS
 */
?>
<?=ProjectPanel::widget(['project' => $project])?>

<h4><?=Yii::t('project', 'Changes')?></h4>
<div class="list-group">
    <?php foreach ($graph as $item):?>
        <?php
        if (!$item->hasCommitPiece()) {
            continue;
        }
        /* @var $commit BaseCommit */
        $commit = $item->getCommit();
        ?>
        <a class="list-group-item col-md-12" href="<?=Url::to(['commit', 'id' => $commit->getId()])?>">
            <div class="col-md-2">
                <span class="commit-date"><?=$commit->getDate()->format('d\'M y H:i:s')?></span>
            </div>
            <div class="col-md-7">
                <strong class="list-group-item-heading"><?=Html::encode($commit->message)?></strong>
            </div>
            <div class="col-md-3">
                <span class="commit-contributor">
                    <?=Html::encode($commit->contributorName)?>
                    <?php if ($commit->contributorEmail && false):?>
                        &lt;<?=Html::encode($commit->contributorEmail)?>&gt;
                    <?php endif;?>
                </span>
            </div>
        </a>
    <?php endforeach;?>
</div>

<?=LinkPager::widget([
    'pagination' => $pagination,
])?>

<?=$this->render('_branches', [
    'branches' => $branches,
])?>