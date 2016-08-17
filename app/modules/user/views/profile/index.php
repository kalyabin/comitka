<?php

use user\models\ChangePasswordForm;
use user\models\ProfileForm;
use user\widgets\ProfileMenu;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $profileForm ProfileForm */
/* @var $changePasswordForm ChangePasswordForm */

$this->title = Yii::t('user', 'Your profile');
?>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('user', 'Common settings') ?></div>
    <?php
    $form = ActiveForm::begin([
        'id' => $profileForm->formName(),
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validateOnBlur' => false,
    ]);
    ?>
    <div class="panel-body">
        <?= $form->field($profileForm, 'email')->staticControl() ?>
        <?= $form->field($profileForm, 'name')->textInput() ?>
    </div>
    <div class="panel-footer">
        <?= Html::submitButton(Yii::t('user', 'Change'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('user', 'Change password') ?></div>
    <?php
    $form = ActiveForm::begin([
        'id' => $changePasswordForm->formName(),
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validateOnBlur' => false,
    ]);
    ?>
    <div class="panel-body">
        <?= $form->field($changePasswordForm, 'password')->passwordInput() ?>
        <?= $form->field($changePasswordForm, 'confirmPassword')->passwordInput() ?>
    </div>
    <div class="panel-footer">
        <?= Html::submitButton(Yii::t('user', 'Change'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$this->blocks['left_block'] = ProfileMenu::widget([
    'authUser' => Yii::$app->user->identity,
    'options' => [
        'class' => 'nav nav-pills nav-stacked',
    ],
]);
?>
