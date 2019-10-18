<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;
use Slim\Views\Twig as TwigView;

/**
 * Session facade class
 *
 * @method @method fetch(string $template, array $data = [])
 * @method fetchBlock(string $template, string $block, array $data = [])
 * @method fetchFromString(string $string = "", array $data = [])
 * @method render(\Psr\Http\Message\ResponseInterface $response, string $template, array $data = [])
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class View extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = TwigView::class;
}
