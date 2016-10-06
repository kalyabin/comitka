<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(dirname(__DIR__)));

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');

// create testing repository
$gitProjectPath = require YII_APP_BASE_PATH . '/../vendor/kalyabin/yii2-git-view/tests/create_repository.php';
define('GIT_PROJECT_PATH', $gitProjectPath);

$hgProjectPath = require YII_APP_BASE_PATH . '/../vendor/kalyabin/yii2-hg-view/tests/create_repository.php';
define('HG_PROJECT_PATH', $hgProjectPath);

Yii::setAlias('@tests', dirname(__DIR__));
