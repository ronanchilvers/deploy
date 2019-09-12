<?php
$settings = include(__DIR__ . '/config/settings.php');
$config   = $settings['database'];

return [

    // General settings
    'version_order' => 'creation',

    // File paths
    'paths' => [
        'migrations' => [
            __DIR__ . '/resources/database/migrations',
        ],
        'seeds' => [
            __DIR__ . '/resources/database/seeds',
        ],
    ],

    'environments' => [

        // Phinx defaults
        'default_migration_table' => 'phinxlog',
        'default_database'        => 'default',

        // Database definition
        'default' => [
            'name'       => $config['name'],
            'connection' => new PDO(
                $config['dsn'],
                $config['username'],
                $config['password'],
                $config['options']
            ),
        ],

    ],

];
