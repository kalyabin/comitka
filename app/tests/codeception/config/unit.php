<?php
/**
 * Application configuration for unit tests
 */
return \yii\helpers\ArrayHelper::merge(include __DIR__ . '/common.php', [
    'class' => 'yii\console\Application',
    'id' => 'unit-test',
], include __DIR__ . '/unit.local.php');
