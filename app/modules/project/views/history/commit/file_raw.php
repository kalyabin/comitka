<?php

use project\controllers\actions\FileViewAction;
use project\models\Project;
use VcsCommon\BaseCommit;
use VcsCommon\BaseDiff;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $project Project */
/* @var $commit BaseCommit */
/* @var $fileContents string */
/* @var $diffs BaseDiff[] */
/* @var $path string */
/* @var $isBinary boolean */
?>

<?php if ($isBinary && $project->repo_type === Project::REPO_GIT):?>
    <p><?= Yii::t('project', 'View <a href="{link}" target="_blank">binary file</a>', [
        'link' => Url::to(['/project/history/file-view',
            'id' => $project->id,
            'commitId' => $commit->id,
            'filePath' => $path,
            'mode' => FileViewAction::MODE_RAW_BINARY,
        ])
    ]) ?></p>
<?php elseif ($isBinary):?>
    <p><?= Yii::t('project', 'Binary file {path} has changed', [
        'path' => $path,
    ]) ?></p>
<?php else:?>
<pre class="raw-file"><?= Html::encode($fileContents) ?></pre>
<?php endif;?>
