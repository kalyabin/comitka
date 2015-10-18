<?php
use user\models\ChangePasswordForm;
use yii\web\View;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this View */
/* @var $model ChangePasswordForm */
print Html::tag('h3', Yii::t('user', 'Set new password'));
$form = ActiveForm::begin([
    'id' => 'change-password',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
    'validateOnSubmit' => true,
    'validateOnChange' => false,
    'validateOnType' => false,
    'validateOnBlur' => false,
]);
    print $form->field($model, 'password')->passwordInput();
    print $form->field($model, 'confirmPassword')->passwordInput();
    print Html::submitButton(Yii::t('user', 'Change password'), ['class' => 'btn btn-primary']);
ActiveForm::end();
