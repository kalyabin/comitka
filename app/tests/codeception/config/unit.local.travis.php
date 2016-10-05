<?php
/**
 * Unit test local config for travis CI
 */

$gitProjectPath = require YII_APP_BASE_PATH . '/../vendor/kalyabin/yii2-git-view/tests/create_repository.php';
$hgProjectPath = require YII_APP_BASE_PATH . '/../vendor/kalyabin/yii2-hg-view/tests/create_repository.php';

return [
    'params' => [
        'testingVariables' => [
            'gitProjectPath' => $gitProjectPath,
            'hgProjectPath' => $hgProjectPath,
        ],
    ],
];
