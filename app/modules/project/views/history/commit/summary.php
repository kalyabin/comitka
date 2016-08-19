<?php

use project\assets\CommitSummaryAsset;
use project\models\Project;
use project\widgets\ProjectPanel;
use project\widgets\RevisionFile;
use user\widgets\ContributorLine;
use VcsCommon\BaseCommit;
use VcsCommon\BaseRepository;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $project Project */
/* @var $repository BaseRepository */
/* @var $commit BaseCommit */
?>
<?=ProjectPanel::widget(['project' => $project])?>

<h4><?=Html::encode($commit->message)?></h4>

<div class="alert alert-info">
    <strong><?=Html::encode($commit->getId())?></strong>
    <?php if (($parents = $commit->getParentsId()) !== false):?>
        (<?= Yii::t('project', 'parents') ?>: <?= implode(', ', array_map(function($parentId) use ($project) {
            return Html::a(
                $parentId,
                [
                    'commit-summary',
                    'id' => $project->getPrimaryKey(),
                    'commitId' => $parentId,
                ]
            );
        }, $commit->getParentsId())) ?>)
    <?php endif;?>
    <br />
    <?= ContributorLine::widget([
        'contributorName' => $commit->contributorName,
        'contributorEmail' => $commit->contributorEmail,
        'vcsType' => $project->repo_type,
    ]) ?>
    <?= Yii::t('project', 'at') ?> <?= Html::encode($commit->getDate()->format("d\'M y H:i:s")) ?>
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
];
CommitSummaryAsset::register($this, $jsOptions);
?>
