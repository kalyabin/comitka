<?php
/**
 * Console application config
 */

// rewrite console config using local config
$localCfg = is_file(__DIR__ . '/console.local.php') ? include __DIR__ . '/console.local.php' : [];
// common config
$common = include __DIR__ . '/common.php';

return \yii\helpers\ArrayHelper::merge($common, [
    'id' => 'console',
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'urlManager' => [
            'hostInfo' => $common['params']['local']['host']['info'],
            'baseUrl' => $common['params']['local']['host']['baseUrl'],
        ],
    ],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
], $localCfg);
