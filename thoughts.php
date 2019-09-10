<?php

$initialise = new Plan('initialise');
$initialise
    ->addAction(
        new CreateWorkspaceAction()
    )
    ->addAction(
        new CreateReleaseAction()
    )
    ->addAction(
        new HookAction()
    )
    ->addAction(
        new NotificationAction()
    )
;

$prepare = new Plan('prepare');
$prepare
    ->addAction(
        new CheckoutAction()
    )
    ->addAction(
        new ComposerAction()
    )
    ->addAction(
        new SharedLocationsAction()
    )
    ->addAction(
        new HookAction()
    )
    ->addAction(
        new NotificationAction()
    )
;

$finalise = new Plan('finalise');
$finalise
    ->addAction(
        new CompleteReleaseAction()
    )
    ->addAction(
        new SymlinkAction()
    )
    ->addAction(
        new HookAction()
    )
    ->addAction(
        new NotificationAction()
    )
;


$configuration = new Configuration;

class QueryService {
    public function updateConfiguration(Configuration $configuration)
    {
        $this->scanRepository(
            $this->project
        );
    }

    protected function scanRepository(Project $project)
    {
        // Contact Github and get deploy.yaml contents
    }
}

$queryService = QueryServiceFactory::forDeployment(
    $project
);
$queryService->updateConfiguration(
    $configuration
);

$release = new Release();
$release->project = $project;

$builder = new Builder(
    $project,
    $release,
    $configuration
);
$builder
    ->addAction(
        new CreateWorkspaceAction()
    )
    ->addAction(
        new CheckoutAction()
    )
    ->addAction(
        new ComposerAction()
    )
    ->addAction(
        new SharedLocationsAction()
    )
    ->addAction(
        new CompleteReleaseAction()
    )
    ->addAction(
        new SymlinkAction()
    )
    ->addAction(
        new HookAction()
    )
;

$context = new Context();
$context->set('project', $project)
    ->set('release', $release)
    ->set('configuration', $configuration)
;

foreach ($this->actions as $stage => $actions) {
    foreach ($actions as $action) {
        $action->run(
            $context
        );
    }
}
