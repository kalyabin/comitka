<?php

use app\components\ContributorApi;
use app\widgets\ContributorLine;
use project\models\Project;
use project\widgets\ProjectPanel;
use VcsCommon\BaseBranch;
use VcsCommon\BaseCommit;
use VcsCommon\BaseRepository;
use yii\bootstrap\Html;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\LinkPager;

/* @var $this View */
/* @var $project Project */
/* @var $history BaseCommit[] */
/* @var $pagination Pagination */
/* @var $branches BaseBranch[] */
/* @var $repository BaseRepository */
/* @var $path string */
/* @var $contributorApi ContributorApi */
$contributorApi = Yii::$app->contributors;
?>
<?=ProjectPanel::widget(['project' => $project])?>

<?php if (!is_null($path)):?>
    <h4>
        <?= Html::encode(Yii::t('project', 'Change log for {path}', [
            'path' => $path,
        ])) ?>

        <?php if (file_exists($repository->getProjectPath() . DIRECTORY_SEPARATOR . dirname($path))):?>
            <?= Html::a('[tree]', [
                '/project/tree/raw',
                'id' => $project->id,
                'path' => dirname($path),
            ]) ?>
        <?php endif;?>
    </h4>
<?php else:?>
    <h4><?=Yii::t('project', 'Change log')?></h4>
<?php endif;?>

<div class="list-group">
    <?php foreach ($history as $commit):?>
        <?php /* @var $commit BaseCommit */ ?>
        <a class="list-group-item col-md-12 history-simple-item" href="<?=Url::to(['commit-summary', 'id' => $project->id, 'commitId' => $commit->getId()])?>">
            <div class="col-md-4">
                <?= ContributorLine::widget([
                    'contributor' => $contributorApi->getContributor($project->repo_type, $commit->contributorName, $commit->contributorEmail),
                    'avatarSize' => 'small',
                    'useLink' => false,
                ]) ?><br />
                <span class="label label-info"><?= Html::encode($project->title) ?></span>
                <span class="commit-date"><?=$commit->getDate()->format('d\'M y H:i:s')?></span>
            </div>
            <div class="col-md-8 commit-message">
                <strong class="list-group-item-heading"><?=Html::encode($commit->message)?></strong>
            </div>
        </a>
    <?php endforeach;?>
</div>

<?=LinkPager::widget([
    'pagination' => $pagination,
])?>

<?php if (is_null($path)):?>
    <?= $this->render('_branches', [
        'project' => $project,
        'branches' => $branches,
    ]) ?>
<?php endif;?>
