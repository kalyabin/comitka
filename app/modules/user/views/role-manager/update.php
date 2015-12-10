<?php

use user\models\Role as RoleForm;
use yii\bootstrap\Html;
use yii\web\View;

/* @var $this View */
/* @var $model RoleForm */

print Html::tag('h1', Yii::t('user', 'Update role: {name}', [
    'name' => Html::encode($model->getName()),
]));

print $this->render('_form', [
    'model' => $model,
]);
