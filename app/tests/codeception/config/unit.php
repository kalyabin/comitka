<?php
/**
 * Application configuration for unit tests
 */
return \yii\helpers\ArrayHelper::merge(include YII_APP_BASE_PATH . '/config/console.php', [

], include __DIR__ . '/unit.local.php');
