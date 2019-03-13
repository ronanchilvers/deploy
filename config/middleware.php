<?php

use DavidePastore\Slim\Validation\Validation;
use Respect\Validation\Validator;
// Add middleware here
// Variables available :
//   - $container
//   - $app

$app->add(new \Ronanchilvers\Sessions\SessionMiddleware(
    $container->get('session')
));
