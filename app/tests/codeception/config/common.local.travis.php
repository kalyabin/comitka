<?php
/**
 * Application configuration for Travis CI
 */
return [
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=comitka_tests',
            'username' => 'travis',
            'password' => '',
        ],
    ],
];
