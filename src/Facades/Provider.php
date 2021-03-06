<?php

namespace App\Facades;

use App\Provider\Factory;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Provider factory facade class
 *
 * @method static \App\Provider\ProviderInterface forProject(\App\Model\Project $project)
 * @method static array getOptions()
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Provider extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = Factory::class;
}
