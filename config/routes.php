<?php
// Add routes here
// Variables available :
//   - $container
//   - $app

use App\App;
use App\Controller\ProjectController;
use App\Controller\Project\ApiController;
use App\Controller\Project\SettingsController;
use App\Controller\UserController;
use App\Controller\UsersController;
use App\Controller\Users\InvitationController;
use App\Facades\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app->get('/', function(ServerRequestInterface $request, ResponseInterface $response) {
    return $response->withRedirect(
        Router::pathFor('project.index')
    );
});

$app->group('/projects', function(App $app) {

    // General
    $app->get('', ProjectController::class . ':index')
        ->setName('project.index');
    $app->map(['GET', 'POST'], '/add', ProjectController::class . ':add')
        ->setName('project.add');

    // Project
    $app->map(['GET'], '/{key}/prepare-deploy', ProjectController::class . ':prepareDeploy')
        ->setName('project.prepare-deploy');
    $app->map(['POST'], '/{key}/deploy', ProjectController::class . ':deploy')
        ->setName('project.deploy');
    $app->map(['GET', 'POST'], '/{key}/deploy/{deployment}', ProjectController::class . ':redeploy')
        ->setName('project.redeploy');
    $app->map(['GET', 'POST'], '/{key}', ProjectController::class . ':view')
        ->setName('project.view');

    // Settings
    $app->map(['GET', 'POST'], '/{key}/edit', SettingsController::class . ':edit')
        ->setName('project.edit');
    $app->map(['GET', 'POST'], '/{key}/webhooks', SettingsController::class . ':webhooks')
        ->setName('project.webhooks');

});

$app->group('/api/project', function(App $app) {
    $app->map(['GET'], '/{key}/events/{number}', ApiController::class . ':events');
});

// Webhook route is outside normal API routing, mainly for obscurity and brevity
$app->get('/d/{token}', ApiController::class . ':webhookDeploy')
    ->setName('project.webhook');

// Individual user routes
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

// User admin routes
$app->group('/users', function(App $app) {

    // User administration
    $app->map(['GET', 'POST'], '/list', UsersController::class . ':index')
        ->setName('users.index');
    $app->map(['GET'], '/toggle-level/{id}', UsersController::class . ':toggleLevel')
        ->setName('users.toggle.level');
    $app->map(['GET'], '/toggle-status/{id}', UsersController::class . ':toggleStatus')
        ->setName('users.toggle.status');

    // Invitations
    $app->map(['GET', 'POST'], '/invitation/create', InvitationController::class . ':create')
        ->setName('users.invite.create');
    $app->map(['GET', 'POST'], '/invitation/{hash}', InvitationController::class . ':accept')
        ->setName('users.invite');

});
