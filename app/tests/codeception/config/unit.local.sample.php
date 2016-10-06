<?php
/**
 * Unit test local config
 */
return [
    'params' => [
        'testingVariables' => [
            // save it for continuous integration
            'gitProjectPath' => GIT_PROJECT_PATH,
            'hgProjectPath' => HG_PROJECT_PATH,
        ],
    ],
];
