<?php

use yii\helpers\ArrayHelper;

/**
 * Functional tests configuration
 */

return ArrayHelper::merge(include YII_APP_BASE_PATH . '/config/common.php', [
    'id' => 'functional-test',

], include __DIR__ . '/functional.local.php');
