<?php

/**
 * Acceptance tests configuration
 */

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include YII_APP_BASE_PATH . '/config/common.php', [
    'id' => 'acceptance-test',

], include __DIR__ . '/acceptance.local.php');
