<?php

namespace App\Test\Action;

use App\Action\AbstractAction;
use App\Action\Context;
use Ronanchilvers\Foundation\Config;

/**
 * Dummy action to allow testing App\Action\AbstractAction
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class TestAbstractAction extends AbstractAction
{
    public function run(Config $configuration, Context $context)
    {}
}
