<?php

use user\models\UserForm;
use yii\bootstrap\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model UserForm */
/* @var $form ActiveForm */

$form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]);

print Html::tag('h2', Yii::t('user', 'Common settings'));

print $form->field($model, 'name')->textInput(['maxlength' => true]);
print $form->field($model, 'email')->textInput(['maxlength' => true]);

if (!$model->isNewRecord) {
    print $form->field($model, 'newPassword')->passwordInput();
    print $form->field($model, 'newPasswordConfirmation')->passwordInput();
    print $form->field($model, 'generateRandomPassword')->checkbox();
}

print $form->field($model, 'sendNotification')->checkbox();

print Html::tag('h2', Yii::t('user', 'Permissions'));
print $form->field($model, 'roles')->checkboxList($model->getRolesList());

if ($model->isNewRecord) {
    print Html::submitButton(Yii::t('user', 'Create'), ['class' => 'btn btn-primary']);
}
else {
    if (Yii::$app->user->can('updateUser')) {
        print Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-primary']);
    }
    if ($model->id != Yii::$app->user->getId() && Yii::$app->user->can('deleteUser') && $model->canSignIn()) {
        print ' ';
        print Html::a(Yii::t('user', 'Lock user'), ['lock', 'id' => $model->id], ['class' => 'btn btn-danger']);
    }
    else if ($model->id != Yii::$app->user->getId() && Yii::$app->user->can('deleteUser') && !$model->canSignIn()) {
        print ' ';
        print Html::a(Yii::t('user', 'Activate user'), ['activate', 'id' => $model->id], ['class' => 'btn btn-success']);
    }
}
print ' ';
print Html::a(Yii::t('user', 'Cancel'), ['index'], ['class' => 'btn btn-default']);

ActiveForm::end();
