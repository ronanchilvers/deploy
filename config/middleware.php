<?php
use App\Security\Middleware\AuthenticationMiddleware;
use Ronanchilvers\Sessions\Middleware\Psr7;

$app->add(new AuthenticationMiddleware());
$app->add(new Psr7(
    $container->get('session')
));
