<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;
use Slim\Views\Twig as TwigView;

/**
 * Session facade class
 *
 * @method static string fetch(string $template, array $data = [])
 * @method static string fetchBlock(string $template, string $block, array $data = [])
 * @method static string fetchFromString(string $string = "", array $data = [])
 * @method static \Psr\Http\Message\ResponseInterface render(\Psr\Http\Message\ResponseInterface $response, string $template, array $data = [])
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class View extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = TwigView::class;
}
