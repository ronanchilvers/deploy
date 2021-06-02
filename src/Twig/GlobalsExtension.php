<?php

namespace App\Twig;

use App\Facades\Session;
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
     * @var array
     */
    protected $globals;

    /**
     * Class constructor
     *
     * @param array $globals
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(array $globals)
    {
        $this->globals = $globals;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getGlobals(): array
    {
        return $this->globals;
    }
}
