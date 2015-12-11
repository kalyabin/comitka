<?php
use user\models\ChangePasswordForm;
use user\models\ProfileForm;
use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap\ActiveForm;

/* @var $this View */
/* @var $profileForm ProfileForm */
/* @var $changePasswordForm ChangePasswordForm */

print Html::tag('h1', Yii::t('user', 'Your profile'));
$form = ActiveForm::begin([
    'id' => $profileForm->formName(),
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
    'validateOnBlur' => false,
]);
    print $form->field($profileForm, 'email')->staticControl();
    print $form->field($profileForm, 'name')->textInput();
    print Html::submitButton(Yii::t('user', 'Change'), ['class' => 'btn btn-primary']);
ActiveForm::end();

print Html::tag('h3', Yii::t('user', 'Change password'));
$form = ActiveForm::begin([
    'id' => $changePasswordForm->formName(),
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
    'validateOnBlur' => false,
]);
    print $form->field($changePasswordForm, 'password')->passwordInput();
    print $form->field($changePasswordForm, 'confirmPassword')->passwordInput();
    print Html::submitButton(Yii::t('user', 'Change'), ['class' => 'btn btn-primary']);
ActiveForm::end();
