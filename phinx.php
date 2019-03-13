<?php
$settings = include(__DIR__ . '/config/settings.php');
$config   = $settings['database'];
$suffix   = '';
if ('sqlite' == $config['driver']) {
    $name = $config['database'];
    $suffix = pathinfo($name, PATHINFO_EXTENSION);
    $name = str_replace('.' . $suffix, '', $name);
}

return [

    // General settings
    'version_order' => 'creation',

    // File paths
    'paths' => [
        'migrations' => [
            __DIR__ . '/config/database/migrations',
        ],
        'seeds' => [
            __DIR__ . '/config/database/seeds',
        ],
    ],

    'environments' => [

        // Phinx defaults
        'default_migration_table' => 'phinxlog',
        'default_database'        => 'default',

        // Database definition
        'default' => [
            'adapter'   => $config['driver'],
            'host'      => $config['host'],
            'port'      => $config['port'],
            'name'      => $name,
            'suffix'    => $suffix,
            'user'      => $config['username'],
            'pass'      => $config['password'],
            'charset'   => $config['charset'],
            'collation' => $config['collation'],
        ],

    ],

];
