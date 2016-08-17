<?php

use user\models\UserForm;
use yii\bootstrap\Html;
use yii\web\View;

/* @var $this View */
/* @var $model UserForm */

$this->title = Yii::t('user', 'Create new user');

print $this->render('_form', [
    'model' => $model,
]);
