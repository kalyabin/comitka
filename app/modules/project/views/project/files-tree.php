<?php

use project\models\Project;
use project\widgets\ProjectPanel;
use VcsCommon\BaseRepository;
use VcsCommon\Directory;
use VcsCommon\File;
use VcsCommon\FileLink;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $project Project */
/* @var $repository BaseRepository */
/* @var $filesList File[] */
/* @var $currentPath array */
?>

<?= ProjectPanel::widget(['project' => $project]) ?>

<h4><?= Yii::t('project', 'Tree') ?></h4>

<div class="project-tree-path">
    <?php reset($currentPath); while ($path = current($currentPath)):?>
        <strong><?= Html::a(Html::encode($path['value']), [
            '/project/project/tree',
            'id' => $project->id,
            'subDir' => $path['subDir'],
        ]) ?></strong>
        <?php if (next($currentPath)):?>
            <?= DIRECTORY_SEPARATOR ?>
        <?php endif;?>
    <?php endwhile;?>

</div>
<table class="project-tree table table-striped table-bordered">
    <?php foreach ($filesList as $file):?>
    <tr>
        <td class="project-tree-file-permissions"><?= $file->getPermissions() ?></td>
        <td class="project-tree-file-size"><?= $file->getSize() ?></td>
        <td class="project-tree-file-path">
            <?php if ($file instanceof Directory):?>
                <?= Html::a(Html::encode(basename($file->getPathname())), [
                    '/project/project/tree',
                    'id' => $project->id,
                    'subDir' => $file->getRelativePath(),
                ]) ?>
            <?php else:?>
                <?= Html::encode(basename($file->getPathname())) ?>
            <?php endif;?>
        </td>
        <td class="project-tree-file-links">
            <?php if ($file instanceof Directory):?>
                <?= Html::a('[tree]', [
                    '/project/project/tree',
                    'id' => $project->id,
                    'subDir' => $file->getRelativePath(),
                ]) ?>
            <?php elseif (!$file instanceof FileLink):?>
                <?= Html::a('[raw]', [
                    '/project/project/raw',
                    'id' => $project->id,
                    'path' => $file->getRelativePath(),
                ]) ?>
            <?php endif;?>

            <?php if ($repository->pathIsNotIgnored($file->getRelativePath())):?>
                <?= Html::a('[history]', [
                    '/project/history/path-history',
                    'id' => $project->id,
                    'path' => $file->getRelativePath(),
                ]) ?>
            <?php endif;?>
        </td>
    </tr>
    <?php endforeach;?>
</table>
