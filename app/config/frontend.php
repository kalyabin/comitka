<?php
/**
 * Frontend application config
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$common = include __DIR__ . '/common.php';

return ArrayHelper::merge($common, [
    'id' => 'frontend',
    'components' => [
        'request' => [
            'cookieValidationKey' => md5(Json::encode($common['params']['local'])),
        ],
        'assetManager' => [
            'linkAssets' => true,
        ],
        'systemAlert' => [
            'class' => '\app\components\Alert',
        ],
        'user' => [
            'class' => '\user\components\Auth',
            'identityClass' => '\user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/user/auth/sign-in'],
        ]
    ],
]);