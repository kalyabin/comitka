<?php
use yii\web\View;

/* @var $this View */
/* @var $content string */

if (isset($user) && $user instanceof user\models\User) {
    // user welcome
    print Yii::t('user', 'Hello, {userName}!', [
        'userName' => $user->name,
    ]);
    print '<br /><br />';
}

print $content;
