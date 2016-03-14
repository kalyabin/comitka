<?php

use user\widgets\ContributorLine;
use VcsCommon\BaseCommit;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $commit BaseCommit */
/* @var $fileContents string */

?>

<p class="raw-file-description">
    <strong><?= Yii::t('project', 'Author') ?>:</strong>
    <?= ContributorLine::widget([
        'contributorName' => $commit->contributorName,
        'contributorEmail' => $commit->contributorEmail,
    ]) ?><br />
</p>

<pre class="raw-file"><?= Html::encode($fileContents) ?></pre>
