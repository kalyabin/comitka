<?php

use user\models\User;
use yii\web\View;

/* @var $this View */
/* @var $user User */
/* @var $newPassword string */

print Yii::t('user', 'Your password hase been reset at <a href="{link}">{link}</a>', [
    'link' => Yii::$app->urlManager->createAbsoluteUrl('/'),
]);
print '<br /><br />';
print Yii::t('user', 'Your new password is:');
print '<br /><br />';
print $newPassword;
print '<br /><br />';