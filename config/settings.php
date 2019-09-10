<?php

use Symfony\Component\Process\PhpExecutableFinder;

$settings = [
    // Slim3 settings
    'displayErrorDetails' => false,

    // Binaries
    'binary' => [
        'php' => '/usr/bin/php',
    ],

    // Logging
    'logger' => [
        'filename' => false
    ],

    // Twig
    'twig' => [
        'templates' => __DIR__ . '/../templates',
        'cache' => __DIR__ . '/../var/twig',
    ],

    // Session settings
    'session' => [
        'encryption.key' => null,
    ],

    // Database connections
    'database' => [
        'name'     => 'app.sq3',
        'dsn'      => 'sqlite:' . __DIR__ . '/../var/database/app.sq3',
        'username' => '',
        'password' => '',
        'options'  => [],
    ],

    // Deployment settings
    'build' => [
        'temp_dir' => sys_get_temp_dir(),
        'base_dir' => '/Users/ronanchilvers/Personal/build',
        'chmod' => [
            'default_file'    => 0640,
            'default_folder'  => 0750,
            'writable_file'   => 0660,
            'writable_folder' => 0770,
        ]
    ],

    // Provider settings
    'providers' => [
        'github' => [
            'token' => 'changeme',
        ],
    ]
];

$localConfig = __DIR__ . '/../local.config.php';
if (file_exists($localConfig)) {
    $localSettings = include($localConfig);
    $settings = array_replace_recursive($settings, $localSettings);
}

return $settings;
