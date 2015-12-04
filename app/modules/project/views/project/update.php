<?php

use project\models\Project;
use yii\bootstrap\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Project */

print Html::tag('h1', Yii::t('user', 'Update project: {name}', [
    'name' => $model->title,
]));

print $this->render('_form', [
    'model' => $model,
]);