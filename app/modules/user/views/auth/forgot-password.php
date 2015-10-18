<?php
use user\models\ForgotPasswordForm;
use yii\web\View;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this View */
/* @var $model ForgotPasswordForm */
print Html::tag('h3', Yii::t('user', 'Forgot password'));
$form = ActiveForm::begin([
    'id' => 'forgot-password',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]);
    print $form->field($model, 'email')->textInput();
    print Html::submitButton(Yii::t('user', 'Send'), ['class' => 'btn btn-primary']);
ActiveForm::end();
