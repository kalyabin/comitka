<?php

use project\assets\CommitSummaryAsset;
use project\controllers\actions\FileViewAction;
use project\models\Project;
use project\widgets\ProjectPanel;
use VcsCommon\BaseCommit;
use VcsCommon\BaseRepository;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $project Project */
/* @var $repository BaseRepository */
/* @var $commit BaseCommit */
?>
<?=ProjectPanel::widget(['project' => $project])?>

<h4><?=Html::encode($commit->message)?></h4>

<p>
    <strong><?=Yii::t('project', 'Author')?>:</strong>
    <?=Html::encode($commit->contributorName)?>
    <?php if ($commit->contributorEmail):?>
        <?=Html::encode('<' . $commit->contributorEmail . '>')?>
    <?php endif;?>
    <br />
    <strong><?=Yii::t('project', 'Revision')?>:</strong>
    <?=Html::encode($commit->getId())?><br />
    <strong><?=Yii::t('project', 'Parent revision')?>:</strong>
    <?= implode('<br />', array_map(function($parentId) use ($project) {
        return Html::a(
            $parentId,
            [
                'commit-summary',
                'id' => $project->getPrimaryKey(),
                'commitId' => $parentId,
            ]
        );
    }, $commit->getParentsId())) ?><br />
</p>

<h5><?=Yii::t('project', 'Changed files')?>:</h5>

<?php foreach ($commit->getChangedFiles() as $item):?>
    <?php
    $itemClassSuffix = 'default';
    $links = [
        [
            'hash' => http_build_query([
                'commitId' => $commit->getId(),
                'filePath' => $item['path']->getPathname(),
                'mode' => FileViewAction::MODE_RAW,
            ]),
            'label' => '[' . Yii::t('project', 'raw') . ']',
        ],
    ];
    if ($item['status'] == 'R' || $item['status'] == 'D') {
        $itemClassSuffix = 'danger';
    }
    elseif ($item['status'] === 'A') {
        $itemClassSuffix = 'success';
    }
    elseif ($item['status'] === 'M') {
        $links[] = [
            'hash' => http_build_query([
                'commitId' => $commit->getId(),
                'filePath' => $item['path']->getPathname(),
                'mode' => FileViewAction::MODE_DIFF,
            ]),
            'label' => '[' . Yii::t('project', 'diff') . ']',
        ];
    }
    print Html::tag(
        'span',
        $item['status'],
        [
            'class' => 'label label-' . $itemClassSuffix
        ]
    );
    print '&nbsp;&nbsp;&nbsp;' . $item['path']->getPathname() . '&nbsp;&nbsp;';
    print implode('&nbsp;', array_map(function($link) {
        return Html::tag('a', $link['label'], [
            'href' => '#' . $link['hash'],
            'class' => 'js-revision-file',
        ]);
    }, $links));
    ?>
    <br />
<?php endforeach; ?>

<?php
// modal window for view file details
$modal = Modal::begin();
Modal::end();

// JavaScript page options
$jsOptions = [
    'fileDetailsUrl' => Url::to([
        'file-view',
        'id' => $project->getPrimaryKey(),
        'commitId' => $commit->getId(),
    ]),
    'fileViewModalId' => $modal->getId(),
    'fileLinkSelector' => '.js-revision-file',
];
CommitSummaryAsset::register($this, $jsOptions);
?>
