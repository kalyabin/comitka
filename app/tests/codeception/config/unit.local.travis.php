<?php
/**
 * Unit test local config for travis CI
 */

return [
    'params' => [
        'testingVariables' => [
            'gitProjectPath' => realpath(__DIR__ . '/../../../../vendor/kalyabin/yii2-git-view/tests/repo/testing-repo'),
            'hgProjectPath' => realpath(__DIR__ . '/../../../../vendor/kalyabin/yii2-hg-view/tests/repo/testing-repo'),
        ],
    ],
];
