<?php

/**
 * Acceptance tests configuration
 */

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include __DIR__ . '/common.php', [
    'id' => 'acceptance-test',

], include __DIR__ . '/acceptance.local.php');
