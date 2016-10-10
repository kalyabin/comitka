<?php

/**
 * Common tests configuration
 */

use yii\helpers\ArrayHelper;
$localCfgPath = __DIR__ . '/common.local.php';
return ArrayHelper::merge(include YII_APP_BASE_PATH . '/config/console.php', [
    'components' => [
        'db' => [
            // save this config for continuous integration
            'dsn' => 'mysql:host=localhost;dbname=comitka_tests',
            'username' => 'root',
            'password' => getenv('APPVEYOR') ? 'Password12!' : '',
        ],
    ],
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
    ],
], is_file($localCfgPath) ? include $localCfgPath : []);
