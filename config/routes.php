<?php
// Add routes here
// Variables available :
//   - $container
//   - $app

use App\App;
use App\Controller\ProjectController;
use App\Controller\Project\ApiController;
use App\Controller\UserController;
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
$app->map(['GET'], '/prepare-deploy/{key}', ProjectController::class . ':prepareDeploy')
    ->setName('project.prepare-deploy');
$app->map(['POST'], '/deploy/{key}', ProjectController::class . ':deploy')
    ->setName('project.deploy');
$app->map(['GET', 'POST'], '/deploy/{key}/{deployment}', ProjectController::class . ':redeploy')
    ->setName('project.redeploy');

$app->group('/api/project', function (App $app) {
    $app->map(['GET'], '/{key}/branches-and-tags', ApiController::class . ':branchesAndTags');
    $app->map(['GET'], '/{key}/events/{number}', ApiController::class . ':events');
});

$app->group('/user', function(App $app) {
    $app->map(['GET', 'POST'], '/login', UserController::class . ':login')
        ->setName('user.login');
    $app->map(['GET'], '/logout', UserController::class . ':logout')
        ->setName('user.logout');
    $app->map(['GET', 'POST'], '/favourite/{project}', UserController::class . ':favourite')
        ->setName('user.favourite');
    $app->map(['GET', 'POST'], '/profile', UserController::class . ':profile')
        ->setName('user.profile');
    $app->map(['GET', 'POST'], '/security', UserController::class . ':security')
        ->setName('user.security');
});
