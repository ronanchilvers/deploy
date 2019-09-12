<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Model\Release;
use Ronanchilvers\Foundation\Config;
use RuntimeException;

/**
 * Action to finalise the release
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class FinaliseAction extends AbstractAction implements ActionInterface
{
    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $release = $context->get('release');
        if (!$release instanceof Release) {
            throw new RuntimeException('Invalid or missing release');
        }
        $release->status = 'deployed';
        if (!$release->save()) {
            throw new RuntimeException('Unable to update the release status');
        }
    }
}
