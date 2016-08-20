<?php
/**
 * Entry point to frontend application
 */

use yii\web\Application;

$rootDir = dirname(__DIR__);
$appDir = $rootDir . '/app';

// include environment constants
include_once $appDir . '/config/env.php';
// include vendors autoload
include $rootDir . '/vendor/autoload.php';
// include yii2 application manually
include $rootDir . '/vendor/yiisoft/yii2/Yii.php';
// get frontend configuration
$config = include $appDir . '/config/frontend.php';
// run application
$application = new Application($config);
$application->run();
