<?php

namespace App;

use Psr\Container\ContainerInterface;
use Ronanchilvers\Foundation\Facade\Facade;
use Slim\App as SlimApp;

/**
 * Local application subclass
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class App extends SlimApp
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function run($silent = false)
    {
        $this->boot($this->getContainer());
        return parent::run($silent);
    }

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
    }
}
