<?php
use user\models\User;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $user User */
/* @var $link string */

print Yii::t('user', 'To change your password, please follow link: ');
print '<br /><br />';
print Html::a($link, $link) . '.';