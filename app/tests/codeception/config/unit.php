<?php
/**
 * Application configuration for unit tests
 */
$localCfgPath = __DIR__ . '/unit.local.php';
return \yii\helpers\ArrayHelper::merge(include __DIR__ . '/common.php', [
    'id' => 'unit-test',
    'params' => [
        'testingVariables' => [
            // save it for continuous integration
            'gitProjectPath' => GIT_PROJECT_PATH,
            'hgProjectPath' => HG_PROJECT_PATH,
        ],
    ],
], is_file($localCfgPath) ? include $localCfgPath : []);
