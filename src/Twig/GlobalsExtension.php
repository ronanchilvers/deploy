<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Extension to add standard globals
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class GlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getGlobals()
    {
        return [
            'main_nav' => [
                [
                    'name' => 'Project List',
                    'route' => 'project.index',
                ],
                [
                    'name' => 'Add Project',
                    'route' => 'project.add',
                ],
            ],
        ];
    }
}
