<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(dirname(__DIR__)));

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');

// repositories for tests
// before this run create_repository.php at yii2-git-view and yii2-hg-view
define('GIT_PROJECT_PATH', realpath(YII_APP_BASE_PATH . '/../vendor/kalyabin/yii2-git-view/tests/repo/testing-repo'));
define('HG_PROJECT_PATH', realpath(YII_APP_BASE_PATH . '/../vendor/kalyabin/yii2-hg-view/tests/repo/testing-repo'));

Yii::setAlias('@tests', dirname(__DIR__));
