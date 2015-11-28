<?php
/**
 * UrlManager configuration
 */

return [
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        '/' => 'main/index',
        '/sign-in' => '/user/auth/sign-in',
        '/sign-out' => '/user/auth/sign-out',
        '/forgot-password' => '/user/auth/forgot-password',
        '/change-password/<hash:([\w]+)>' => '/user/auth/change-password',
        '/profile' => '/user/profile/index',
        '/users' => '/user/manager/index',
        '/users/create' => '/user/manager/create',
        '/users/<action:(update|lock|activate)>/<id:(\d+)>' => '/user/manager/<action>',
    ],
];