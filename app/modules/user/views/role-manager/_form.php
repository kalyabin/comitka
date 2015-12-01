<?php

use user\models\Role as RoleForm;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model RoleForm */
/* @var $form ActiveForm */

$form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]);

print $form->field($model, 'name')->textInput([
    'maxlength' => true,
    'readonly' => !$model->getIsNewRecord(),
]);
print $form->field($model, 'description')->textInput(['maxlength' => true]);

print $form->field($model, 'permissions', [
    'template' => '{label}{hint}{input}{error}',
])->checkboxList(
    ArrayHelper::map($model->getAllPermissions(), 'name', 'description'),
    [
        'itemOptions' => ['labelOptions' => ['style' => 'display:block']]
    ]
);

if ($model->getIsNewRecord()) {
    print Html::submitButton(Yii::t('user', 'Create'), ['class' => 'btn btn-primary']);
}
else {
    print Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-primary']);
}
print ' ';
print Html::a(Yii::t('user', 'Cancel'), ['index'], ['class' => 'btn btn-default']);

ActiveForm::end();
