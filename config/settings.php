<?php

$settings = [
    // Slim3 settings
    'displayErrorDetails' => false,

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
        'driver'   => 'sqlite',
        'host'     => '',
        'port'     => '',
        'database' => __DIR__ . '/../var/database/app.sq3',
        'username' => '',
        'password' => '',
        'charset'  => 'utf8',
        'collation'=> 'utf8_unicode_ci',
        'prefix'   => '',
    ],
];

$localConfig = __DIR__ . '/../local.config.php';
if (file_exists($localConfig)) {
    $localSettings = include($localConfig);
    $settings = array_replace_recursive($settings, $localSettings);
}

return $settings;
