<?php

use project\models\Project;
use project\widgets\ProjectPanel;
use VcsCommon\BaseRepository;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $project Project */
/* @var $repository BaseRepository */
/* @var $fileContents string */
/* @var $currentPath array */
?>

<?= ProjectPanel::widget(['project' => $project]) ?>

<h4><?= Yii::t('project', 'Tree') ?></h4>

<?= $this->render('_breadcrumbs', [
    'project' => $project,
    'breadcrumbs' => $breadcrumbs,
]) ?>

<pre class="raw-file"><?= Html::encode($fileContents) ?></pre>
