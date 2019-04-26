<?php

namespace App\Traits;

use App\Model\Observer\ProjectObserver;
use App\Model\Project;
use Psr\Container\ContainerInterface;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Trait providing methods for booting the application
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait BootTrait
{
    /**
     * Boot the framework
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function boot(ContainerInterface $container)
    {
        // Configure facades
        Facade::setContainer($container);

        // Boot eloquent
        $capsule = $container->get('eloquent.capsule');
        $capsule->bootEloquent();

        Project::observe(new ProjectObserver);
    }
}
