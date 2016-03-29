<?php

use yii\web\View;
use project\models\Project;
use yii\helpers\Html;

/* @var $this View */
/* @var $project Project */
/* @var $breadcrumbs array */
?>

<div class="project-tree-path">
    <?php reset($breadcrumbs); while ($path = current($breadcrumbs)):?>
        <?php if (next($breadcrumbs)):?>
            <strong>
                <?= Html::a(Html::encode($path['value']), [
                    '/project/tree/raw',
                    'id' => $project->id,
                    'path' => $path['path'],
                ]) ?>
            </strong>
            <?= DIRECTORY_SEPARATOR ?>
        <?php else:?>
            <strong><?= Html::encode($path['value']) ?></strong>
        <?php endif;?>
    <?php endwhile;?>
</div>
