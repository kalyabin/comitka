<?php
use yii\web\View;
/* @var $this View */
/* @var $content string */
?>
<?php $this->beginContent('@app/views/layouts/default.php'); ?>
    <?=Yii::$app->systemAlert->viewMessage()?>
    <?=$content?>
<?php $this->endContent(); ?>
