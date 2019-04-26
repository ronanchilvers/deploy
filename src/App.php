<?php

namespace App;

use App\Traits\BootTrait;
use Psr\Container\ContainerInterface;
use Ronanchilvers\Foundation\Facade\Facade;
use Ronanchilvers\Foundation\Slim\App as SlimApp;

/**
 * Local application subclass
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class App extends SlimApp
{
    use BootTrait;
}
