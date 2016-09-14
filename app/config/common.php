<?php
/**
 * Common application config
 */

use yii\helpers\ArrayHelper;

// local system params as database connection, base_url or mail params
$localParams = include __DIR__ . '/params.php';
// rewrite common using local config
$localCfg = is_file(__DIR__ . '/common.local.php') ? include __DIR__ . '/common.local.php' : [];

$mailerConfig = [
    'class' => 'app\components\Mailer',
    'useFileTransport' => !isset($localParams['smtp']) && defined('YII_ENV_DEV') && YII_ENV_DEV === true,
    'viewPath' => '@app/mail',
    'messageConfig' => [
        'charset' => 'UTF-8',
    ],
];

if (isset($localParams['smtp'])) {
    $mailerConfig = ArrayHelper::merge([
        'transport' => [
            'class' => 'Swift_SmtpTransport',
            'host' => $localParams['smtp']['host'],
            'username' => $localParams['smtp']['username'],
            'password' => $localParams['smtp']['password'],
            'port' => $localParams['smtp']['port'],
            'encryption' => $localParams['smtp']['encryption'],
        ]
    ], $mailerConfig);
}

return ArrayHelper::merge([
    'basePath' => dirname(__DIR__),
    'vendorPath' => realpath(dirname(__DIR__) . '/../vendor'),
    'sourceLanguage' => 'en-US',
    'bootstrap' => ['log'],
    'aliases' => ArrayHelper::merge([
        '@' => dirname(__DIR__),
        '@app' => dirname(__DIR__),
    ], include __DIR__ . '/aliases.php'),
    'components' => [
        'mailer' => $mailerConfig,
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
        'gitWrapper' => [
            'class' => 'GitView\GitWrapper',
            'cmd' => isset($localParams['git']['cmd']) ? $localParams['git']['cmd'] : 'git',
        ],
        'hgWrapper' => [
            'class' => 'HgView\HgWrapper',
            'cmd' => isset($localParams['hg']['cmd']) ? $localParams['hg']['cmd'] : 'hg',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf8',
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
    'modules' => [
        'user' => [
            'class' => 'user\UserModule',
        ],
        'project' => [
            'class' => 'project\ProjectModule',
        ],
    ],
    'params' => [
        'local' => $localParams,
    ],
], $localCfg);
