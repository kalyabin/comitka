<?php
/**
 * Application configuration shared by all test types
 */
return [
    'components' => [
        'db' => [
            // save this config for continuous integration
            'dsn' => 'mysql:host=localhost;dbname=comitka_tests',
            'username' => 'root',
            'password' => getenv('APPVEYOR') ? 'Password12!' : '',
        ],
    ],
];
