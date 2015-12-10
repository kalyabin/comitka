<?php

use user\models\Role as RoleForm;
use yii\bootstrap\Html;
use yii\web\View;

/* @var $this View */
/* @var $model RoleForm */

print Html::tag('h1', Yii::t('user', 'Create new role'));

print $this->render('_form', [
    'model' => $model,
]);
