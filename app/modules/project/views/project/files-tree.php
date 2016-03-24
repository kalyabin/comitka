<?php

use project\models\Project;
use project\widgets\ProjectPanel;
use VcsCommon\BaseRepository;
use VcsCommon\Directory;
use VcsCommon\File;
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
<table class="project-tree">
    <?php foreach ($filesList as $k => $file):?>
    <tr<?php if ($k % 2 == 0):?> class="even"<?php endif;?>>
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
            <?php else:?>
                <?= Html::a('[raw]', [
                    '/project/project/raw',
                    'id' => $project->id,
                    'path' => $file->getRelativePath(),
                ]) ?>
            <?php endif;?>

            <?= Html::a('[history]', [
                '/project/history/path-history',
                'id' => $project->id,
                'path' => $file->getRelativePath(),
            ]) ?>
        </td>
    </tr>
    <?php endforeach;?>
</table>
