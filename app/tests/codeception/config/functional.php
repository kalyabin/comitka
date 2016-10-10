<?php

use yii\helpers\ArrayHelper;

/**
 * Functional tests configuration
 */

$localCfgPath = __DIR__ . '/functional.local.php';

return ArrayHelper::merge(include __DIR__ . '/common.php', [
    'id' => 'functional-test',

], is_file($localCfgPath) ? $localCfgPath : []);
