<?php

namespace App\Console;

use App\Traits\BootTrait;
use Ronanchilvers\Foundation\Console\Application as BaseApplication;

/**
 * Base console application
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Application extends BaseApplication
{
    use BootTrait;
}
