<?php
// Add services here
// Variables available :
//   - $container

$container->register(new \App\Provider());
$container->register(new \App\Provider\ProviderProvider());
$container->register(new \App\Notifier\NotifierProvider());
$container->register(new \App\Security\SecurityProvider());
$container->register(new \App\Mail\MailProvider(),[
    'mailer_settings' => $container->get('settings')['mail']
]);

// Queue
$container->register(new Ronanchilvers\Foundation\Queue\Provider(), [
    'pheanstalk_settings' => $container->get('settings')['queue'],
]);
