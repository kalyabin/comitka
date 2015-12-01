<?php

use user\models\UserForm;
use yii\bootstrap\Html;
use yii\web\View;

/* @var $this View */
/* @var $model UserForm */

print Html::tag('h1', Yii::t('user', 'Create new user'));

print $this->render('_form', [
    'model' => $model,
]);