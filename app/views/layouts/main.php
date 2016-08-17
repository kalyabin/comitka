<?php
use yii\web\View;
/* @var $this View */
/* @var $content string */
?>
<?php $this->beginContent('@app/views/layouts/default.php'); ?>
    <?php if (!isset($this->blocks['title']) && !empty($this->title)):?>
        <div class="page-header">
            <h1><?= $this->title ?></h1>
        </div>
    <?php elseif (isset($this->blocks['title'])):?>
        <div class="page-header"><?= $this->blocks['title'] ?></div>
    <?php endif;?>

    <?=Yii::$app->systemAlert->viewMessage()?>

    <?php if (isset($this->blocks['left_block'])):?>
        <div class="col-md-3 col-sm-1"><?= $this->blocks['left_block'] ?></div>
        <div class="one-column-container col-md-9 col-sm-1"><?= $content ?></div>
    <?php else:?>
        <?= $content ?>
    <?php endif;?>
<?php $this->endContent(); ?>
