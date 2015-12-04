<?php

use project\models\Project;
use yii\bootstrap\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Project */
/* @var $form ActiveForm */

$form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]);

print $form->field($model, 'title')->textInput(['maxlength' => true]);
print $form->field($model, 'repo_path')->textInput();
print $form->field($model, 'repo_type')->dropDownList($model->getRepoTypeList());

if ($model->isNewRecord) {
    print Html::submitButton(Yii::t('project', 'Create'), ['class' => 'btn btn-primary']);
}
else {
    if (Yii::$app->user->can('updateProject')) {
        print Html::submitButton(Yii::t('project', 'Update'), ['class' => 'btn btn-primary']);
    }
    if (Yii::$app->user->can('deleteProject')) {
        print ' ' ;
        print Html::a(Yii::t('project', 'Delete'), ['delete', 'id' => $model->getPrimaryKey()], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('project', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ]
        ]);
    }
}
print ' ';
print Html::a(Yii::t('user', 'Cancel'), ['index'], ['class' => 'btn btn-default']);

ActiveForm::end();
