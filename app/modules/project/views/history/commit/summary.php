<?php

use project\models\Project;
use project\widgets\ProjectPanel;
use VcsCommon\BaseCommit;
use VcsCommon\BaseRepository;
use yii\bootstrap\Html;
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
    <?=implode('<br />', $commit->getParentsId())?><br />
</p>

<h5><?=Yii::t('project', 'Changed files')?>:</h5>

<?php foreach ($commit->getChangedFiles() as $item):?>
    <?php
    $itemClassSuffix = 'default';
    if ($item['status'] == 'R' || $item['status'] == 'D') {
        $itemClassSuffix = 'danger';
    }
    else if ($item['status'] == 'A') {
        $itemClassSuffix = 'success';
    }
    ?>
    <?=Html::tag('span', $item['status'], ['class' => 'label label-' . $itemClassSuffix]) . '&nbsp;&nbsp;&nbsp;' . $item['path']->getPathname()?>
    <br />
<?php endforeach; ?>
