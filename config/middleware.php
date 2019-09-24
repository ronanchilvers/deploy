<?php
use App\Security\Middleware\AuthenticationMiddleware;
use Ronanchilvers\Sessions\SessionMiddleware;

$app->add(new AuthenticationMiddleware());
$app->add(new SessionMiddleware(
    $container->get('session')
));
