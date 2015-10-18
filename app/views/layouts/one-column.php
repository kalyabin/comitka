<?php
use yii\web\View;
/* @var $this View */
/* @var $content string */
?>
<?php $this->beginContent('@app/views/layouts/default.php');?>
<div class="col-md-3 col-sm-1"></div>
<div class="one-column-container col-md-6 col-sm-10">
    <?=Yii::$app->systemAlert->viewMessage()?>
    <?=$content?>
</div>
<div class="col-md-3 col-sm-1"></div>
<?php $this->endContent(); ?>