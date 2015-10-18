<?php
use user\models\SignInForm;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model SignInForm */
/* @var $form ActiveForm */

print Html::tag('h3', Yii::t('user', 'Sign in'));
$form = ActiveForm::begin([
    'id' => 'sign-in',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
    'validateOnSubmit' => true,
    'validateOnChange' => false,
    'validateOnType' => false,
    'validateOnBlur' => false,
]);
    print $form->field($model, 'email')->textInput();
    print $form->field($model, 'password')->passwordInput();
    print Html::submitButton(Yii::t('user', 'Sign in'), ['class' => 'btn btn-primary']);
    print '&nbsp;&nbsp;';
    print Html::beginTag('strong');
        print Html::a(Yii::t('user', 'Forgot password...'), \yii\helpers\Url::to(['forgot-password']));
    print Html::endTag('strong');
ActiveForm::end();