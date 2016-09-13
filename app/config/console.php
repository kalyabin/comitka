<?php
/**
 * Console application config
 */

// rewrite console config using local config
$localCfg = is_file(__DIR__ . '/console.local.php') ? include __DIR__ . '/console.local.php' : [];
// common config
$common = include __DIR__ . '/common.php';

// development enviroment configuration
if (defined('YII_ENV_DEV') && YII_ENV_DEV === true) {
    $localCfg = \yii\helpers\ArrayHelper::merge([
        'bootstrap' => ['gii'],
        'controllerMap' => [
            'fixture' => [
                'class' => 'yii\faker\FixtureController',
            ],
        ],
        'modules' => [
            'gii' => 'yii\gii\Module',
        ],
    ], $localCfg);
}

return \yii\helpers\ArrayHelper::merge($common, [
    'id' => 'console',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'urlManager' => [
            'hostInfo' => $common['params']['local']['host']['info'],
            'baseUrl' => $common['params']['local']['host']['baseUrl'],
        ],
    ],
], $localCfg);
