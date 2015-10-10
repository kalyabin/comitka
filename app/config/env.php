<?php
/**
 * Environment constants as debug, trace level etc.
 */

if (is_file(__DIR__ . '/env.local.php')) {
    // rewrite using local configuration
    include_once __DIR__ . '/env.local.php';
}

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV_DEV') or define('YII_ENV_DEV', false);