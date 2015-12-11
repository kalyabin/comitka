<?php
/**
 * Application configuration for unit tests
 */
return \yii\helpers\ArrayHelper::merge(include __DIR__ . '/common.php', [
    'id' => 'unit-test',
], include __DIR__ . '/unit.local.php');
