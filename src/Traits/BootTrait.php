<?php

namespace App\Traits;

use App\Facades\Log;
use PDO;
use Psr\Container\ContainerInterface;
use Ronanchilvers\Foundation\Facade\Facade;
use Ronanchilvers\Orm\Orm;

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
        Orm::setConnection($container->get(PDO::class));
        Orm::getEmitter()->on('query.init', function($sql, $params) {
            Log::debug('Query init', [
                'sql'    => $sql,
                'params' => $params,
            ]);
        });
    }
}
