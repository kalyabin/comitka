<?php

use yii\helpers\ArrayHelper;

/**
 * Functional tests configuration
 */

return ArrayHelper::merge(include __DIR__ . '/common.php', [
    'id' => 'functional-test',

], include __DIR__ . '/functional.local.php');
