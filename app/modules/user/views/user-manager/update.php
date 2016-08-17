<?php

use user\models\UserForm;
use user\widgets\UserMenu;
use yii\bootstrap\Html;
use yii\web\View;

/* @var $this View */
/* @var $model UserForm */

$this->title = Html::encode($model->name);

print $this->render('_form', [
    'model' => $model,
]);

$this->blocks['left_block'] = UserMenu::widget([
    'authUser' => Yii::$app->user->identity,
    'model' => $model,
    'options' => [
        'class' => 'nav nav-pills nav-stacked',
    ],
]);
