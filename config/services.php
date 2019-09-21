<?php
// Add services here
// Variables available :
//   - $container

$container->register(new \App\Provider());
$container->register(new \App\Provider\ProviderProvider());
$container->register(new \App\Notifier\NotifierProvider());

// Queue
$container->register(new Ronanchilvers\Foundation\Queue\Provider(), [
    'pheanstalk_settings' => $container->get('settings')['queue'],
]);
