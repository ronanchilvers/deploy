<?php
// Add routes here
// Variables available :
//   - $container
//   - $app

use App\Controller\ProjectController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app->get('/', ProjectController::class . ':index')
    ->setName('project.index');
$app->map(['GET', 'POST'], '/add', ProjectController::class . ':add')
    ->setName('project.add');
$app->map(['GET', 'POST'], '/edit/{id}', ProjectController::class . ':edit')
    ->setName('project.edit');
