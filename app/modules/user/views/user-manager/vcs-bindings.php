<?php

use app\assets\VCSBindingsAsset;
use user\models\User;
use user\models\UserAccountForm;
use user\widgets\UserMenu;
use yii\base\View;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this View */
/* @var $model User */
/* @var $accounts UserAccountForm[] */
/* @var $newAccount UserAccountForm */
/* @var $successMessage string */
/* @var $errorMessage string */

VCSBindingsAsset::register($this);

$this->title = Html::encode($model->name);
?>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('user', 'VCS bindings') ?></div>

    <?php Pjax::begin(['enablePushState' => false]); ?>
        <?= $this->render('vcs-bindings-accounts', [
            'model' => $model,
            'accounts' => $accounts,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
        ]) ?>
    <?php Pjax::end(); ?>
</div>

<?php
$this->blocks['left_block'] = UserMenu::widget([
    'authUser' => Yii::$app->user->identity,
    'model' => $model,
    'options' => [
        'class' => 'nav nav-pills nav-stacked',
    ],
]);
?>
