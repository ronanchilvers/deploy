<?php

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Yaml\Yaml;

$settings = [
    // Slim3 settings
    'displayErrorDetails' => false,
    'determineRouteBeforeAppMiddleware' => true,

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
        'templates' => [
            'web'    => __DIR__ . '/../resources/templates',
            'emails' => __DIR__ . '/../resources/emails',
        ],
        'cache' => false,
    ],

    // Session settings
    'session' => [
        'name'           => 'deploy_session',
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

    // Queue config
    'queue' => [
        'host'          => '127.0.0.1',
        'port'          => 11300,
        'default.queue' => 'deploy',
        'timeout'       => 2,
    ],

    // Emails
    'mail' => [

        'transport' => [
            'host'       => 'smtp.mailtrap.io',
            'port'       => 587,
            'username'   => '7a66388ce93347',
            'password'   => '3a2601de65e889',
            'tls'        => true,
        ],

        'options' => [
            'from'      => 'no-reply@deploy',
            'from_name' => 'deploy',
        ]
    ],

    // Deployment settings
    'build' => [
        'symlink_name' => 'current',
        'temp_dir' => sys_get_temp_dir(),
        'base_dir' => '/Users/ronanchilvers/Personal/build',
        'chmod' => [
            'default_file'    => 0640,
            'default_folder'  => 0750,
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

$localYaml = __DIR__ . '/../local.yaml';
if (file_exists($localYaml)) {
    $localSettings = Yaml::parseFile($localYaml);
    $settings = array_replace_recursive($settings, $localSettings);
}

return $settings;
