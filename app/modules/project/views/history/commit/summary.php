<?php

use app\components\ContributorApi;
use project\assets\CommitSummaryAsset;
use project\models\ContributionReview;
use project\models\Project;
use project\widgets\CommitPanel;
use project\widgets\ProjectPanel;
use project\widgets\RevisionFile;
use VcsCommon\BaseCommit;
use VcsCommon\BaseRepository;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $project Project */
/* @var $repository BaseRepository */
/* @var $commit BaseCommit */
/* @var $reviewModel ContributionReview */
/* @var $contributorApi ContributorApi */
$contributorApi = Yii::$app->contributors;
?>
<?=ProjectPanel::widget(['project' => $project])?>

<h4><?=Html::encode($commit->message)?></h4>

<div class="js-commit-panel">
    <?= CommitPanel::widget([
        'reviewModel' => $reviewModel,
        'authUser' => Yii::$app->user,
        'contributor' => $reviewModel ?
            $reviewModel->contributor :
            $contributorApi->getContributor($project->repo_type, $commit->contributorName, $commit->contributorEmail),
        'project' => $project,
        'commit' => $commit,
        'reviewButtonClass' => 'js-review-button',
    ]) ?>
</div>

<h5><?=Yii::t('project', 'Changed files')?>:</h5>

<?php
foreach ($commit->getChangedFiles() as $item):
    print RevisionFile::widget([
        'repository' => $repository,
        'project' => $project,
        'commit' => $commit,
        'file' => $item,
    ]);
endforeach;

// JavaScript page options
$jsOptions = [
    'fileDetailsUrl' => Url::to([
        'file-view',
        'id' => $project->getPrimaryKey(),
        'commitId' => $commit->getId(),
    ]),
    'fileContentSelector' => '.js-revision-file-content',
    'fileLinkSelector' => '.js-revision-file',
    'fileLinkActiveClass' => 'active',
    'commitPanelSelector' => '.js-commit-panel',
    'reviewButtonSelector' => '.js-review-button',
    'commitRowSelector' => '.js-commit-row',
    'selectedCommitRowClass' => 'selected-row'
];
CommitSummaryAsset::register($this, $jsOptions);
?>
