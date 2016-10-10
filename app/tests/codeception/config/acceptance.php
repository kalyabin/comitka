<?php

/**
 * Acceptance tests configuration
 */

use yii\helpers\ArrayHelper;

$localCfgPath = __DIR__ . '/acceptance.local.php';

return ArrayHelper::merge(include __DIR__ . '/common.php', [
    'id' => 'acceptance-test',

], is_file($localCfgPath) ? include $localCfgPath : []);
