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
$app->map(['GET', 'POST'], '/edit/{key}', ProjectController::class . ':edit')
    ->setName('project.edit');
$app->map(['GET', 'POST'], '/view/{key}', ProjectController::class . ':view')
    ->setName('project.view');
$app->map(['GET', 'POST'], '/deploy/{key}', ProjectController::class . ':deploy')
    ->setName('project.deploy');
