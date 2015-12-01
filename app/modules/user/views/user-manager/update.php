<?php

use user\models\UserForm;
use yii\bootstrap\Html;
use yii\web\View;

/* @var $this View */
/* @var $model UserForm */

print Html::tag('h1', Yii::t('user', 'Update user: {name}', [
    'name' => $model->name,
]));

print $this->render('_form', [
    'model' => $model,
]);