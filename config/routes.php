<?php
// Add routes here
// Variables available :
//   - $container
//   - $app

use App\App;
use App\Controller\Project\ApiController;
use App\Controller\ProjectController;
use App\Controller\UserController;
use App\Facades\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) {
    return $response->withRedirect(
        Router::pathFor('project.index')
    );
});

$app->group('/projects', function (App $app) {
    $app->get('', ProjectController::class . ':index')
        ->setName('project.index');
    $app->map(['GET', 'POST'], '/add', ProjectController::class . ':add')
        ->setName('project.add');
    $app->map(['GET', 'POST'], '/{key}/edit', ProjectController::class . ':edit')
        ->setName('project.edit');
    $app->map(['GET'], '/{key}/prepare-deploy', ProjectController::class . ':prepareDeploy')
        ->setName('project.prepare-deploy');
    $app->map(['POST'], '/{key}/deploy', ProjectController::class . ':deploy')
        ->setName('project.deploy');
    $app->map(['GET', 'POST'], '/{key}/deploy/{deployment}', ProjectController::class . ':redeploy')
        ->setName('project.redeploy');
    $app->map(['GET', 'POST'], '/{key}', ProjectController::class . ':view')
        ->setName('project.view');
});

$app->group('/api/project', function (App $app) {
    $app->map(['GET'], '/{key}/events/{number}', ApiController::class . ':events');
});
// Webhook route is outside normal API routing, mainly for obscurity and brevity
$app->get('/d/{token}', ApiController::class . ':webhookDeploy')
    ->setName('project.webhook');

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
