<?php

use App\App;
use App\Controller\IndexController;
use Ronanchilvers\Container\Slim\Container;

require("../vendor/autoload.php");

$container = new Container([
    'settings' => include('../config/settings.php')
]);

// Load app services
include("../config/services.php");

// Create the App object
$app = new App($container);
include("../config/middleware.php");
include("../config/routes.php");
$app->run();
