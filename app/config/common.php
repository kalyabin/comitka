<?php
/**
 * Common application config
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Json;

// local system params as database connection, base_url or mail params
$localParams = Json::decode(
    file_get_contents(dirname(dirname(__DIR__)) . '/conf.d/parameters.json')
);
// rewrite common using local config
$localCfg = is_file(__DIR__ . '/common.local.php') ? include __DIR__ . '/common.local.php' : [];

$config = ArrayHelper::merge([
    'basePath' => dirname(__DIR__),
    'sourceLanguage' => 'en-US',
    'bootstrap' => ['log'],
    'aliases' => [
        '@' => dirname(__DIR__),
        '@app' => dirname(__DIR__),
    ],
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
            'viewPath' => '@app/mail',
            'messageConfig' => [
                'charset' => 'UTF-8',
            ],
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'forceTranslation' => true,
                    'fileMap' => [],
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf-8',
            'tablePrefix' => 'app_',
            'dsn' => "mysql:host={$localParams['db']['host']};dbname={$localParams['db']['name']}",
            'username' => $localParams['db']['username'],
            'password' => $localParams['db']['password'],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'urlManager' => include __DIR__ . '/urlmanager.php',
        'log' => [
            'traceLevel' => defined('YII_DEBUG') && YII_DEBUG == true ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => [
        'local' => $localParams,
    ],
], $localCfg);

if (defined('YII_ENV_DEV') && YII_ENV_DEV == true) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;