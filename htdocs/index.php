<?php
/**
 * Entry point to frontend application
 */

use yii\web\Application;

$appDir = dirname(__DIR__) . '/app';

// include environment constants
include_once $appDir . '/config/env.php';
// include vendors autoload
include $appDir . '/vendor/autoload.php';
// include yii2 application manually
include $appDir . '/vendor/yiisoft/yii2/Yii.php';
// get frontend configuration
$config = include $appDir . '/config/frontend.php';
// run application
$application = new Application($config);
$application->run();
