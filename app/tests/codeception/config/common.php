<?php

/**
 * Common tests configuration
 */

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include YII_APP_BASE_PATH . '/config/console.php', [
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
    ],
], include __DIR__ . '/common.local.php');
