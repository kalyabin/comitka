<?php
/**
 * Frontend application config
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$common = include __DIR__ . '/common.php';

$config = ArrayHelper::merge($common, [
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

if (defined('YII_ENV_DEV') && YII_ENV_DEV == true) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
