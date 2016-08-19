<?php

use app\assets\HistoryGraphAsset;
use project\models\Project;
use project\widgets\ProjectPanel;
use user\widgets\ContributorLine;
use VcsCommon\BaseBranch;
use VcsCommon\BaseCommit;
use yii\bootstrap\Html;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\LinkPager;

/* @var $this View */
/* @var $project Project */
/* @var $history BaseCommit[] */
/* @var $pagination Pagination */
/* @var $branches BaseBranch[] */

// register client script
HistoryGraphAsset::register($this, [
    'commits' => array_values(ArrayHelper::map($history, 'id', function($data) {
        /* @var $data BaseCommit */
        return [
            'id' => $data->getId(),
            'level' => $data->graphLevel,
            'parents' => $data->getParentsId(),
        ];
    })),
    'topPadding' => 4,
    'leftPadding' => 10,
    'columnWidth' => 30,
    'commitRadius' => 4,
]);
?>
<?=ProjectPanel::widget(['project' => $project])?>

<h4><?=Yii::t('project', 'Change log')?></h4>
<div id="historyGraph"></div>
<div class="list-group" id="historySimple">
    <?php foreach ($history as $commit):?>
    <a class="list-group-item col-md-12 history-simple-item js-history-simple-item" href="<?=Url::to(['commit-summary', 'id' => $project->id, 'commitId' => $commit->getId()])?>">
            <div class="col-md-2">
                <span class="commit-date"><?=$commit->getDate()->format('d\'M y H:i:s')?></span>
            </div>
            <div class="col-md-3">
                <?= ContributorLine::widget([
                    'contributorName' => $commit->contributorName,
                    'vcsType' => $project->repo_type,
                    'avatarSize' => 'small',
                    'useLink' => false,
                ]) ?>
            </div>
            <div class="col-md-7 commit-message">
                <strong class="list-group-item-heading"><?=Html::encode($commit->message)?></strong>
            </div>
        </a>
    <?php endforeach;?>
</div>

<?=LinkPager::widget([
    'pagination' => $pagination,
])?>

<?=$this->render('_branches', [
    'project' => $project,
    'branches' => $branches,
])?>
