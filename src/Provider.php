<?php

namespace App;

use App\Provider\Factory;
use App\Provider\Github;
use App\Security\Manager;
use App\Twig\GlobalsExtension;
use App\Twig\ProjectExtension;
use App\Twig\UserExtension;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Registry;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Ronanchilvers\Container\Container;
use Ronanchilvers\Container\ServiceProviderInterface;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Foundation\Filesystem\Disk;
use Ronanchilvers\Foundation\Filesystem\DiskRegistry;
use Ronanchilvers\Sessions\Session;
use Ronanchilvers\Sessions\Storage\CookieStorage;
use Ronanchilvers\Utility\File;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Symfony\Component\Yaml\Yaml;

/**
 * App service provider
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Provider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $container)
    {
        // Logger
        $container->set(LoggerInterface::class, function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['logger'];
            $logger = new Logger('default');
            if (isset($loggerSettings['filename']) && false !== $loggerSettings['filename']) {
                if (DIRECTORY_SEPARATOR !== substr($loggerSettings['filename'], 0, 1)) {
                    $loggerSettings['filename'] = File::join(
                        __DIR__,
                        '/../',
                        $loggerSettings['filename']
                    );
                }
                $logger->pushHandler(
                    new StreamHandler(
                        $loggerSettings['filename'],
                        Logger::DEBUG
                    )
                );
            }
            Registry::clear();
            Registry::addLogger($logger);

            return $logger;
        });

        // Twig
        $container->set(Twig::class, function (ContainerInterface $c) {
            $settings = $c->get('settings')['twig'];
            $view = new Twig(
                $settings['templates'],
                [
                    'cache' => $settings['cache']
                ]
            );
            $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
            $view->addExtension(
                new TwigExtension(
                    $c->get('router'),
                    $uri
                )
            );
            $view->addExtension(
                new GlobalsExtension([
                    'debug'    => $c->get('settings')['displayErrorDetails'],
                    'session'  => $c->get('session'),
                    'request'  => $c->get('request'),
                    'security' => $c->get(Manager::class),
                    'php' => [
                        'version' => phpversion(),
                    ],
                ])
            );
            $view->addExtension(
                new ProjectExtension($c->get(Factory::class))
            );
            $view->addExtension(
                new UserExtension()
            );

            return $view;
        });

        // Session
        $container->set('session.storage.options', function ($c) {
            return $c->get('settings')['session'];
        });
        $container->share('session.storage', function ($c) {
            $options = $c->get('session.storage.options');

            return new CookieStorage(
                $options
            );
        });
        $container->share('session', function ($c) {
            return new Session(
                $c->get('session.storage')
            );
        });

        // Database
        $container->share(PDO::class, function ($c) {
            $settings = $c->get('settings')['database'];
            return new PDO(
                $settings['dsn'],
                $settings['username'],
                $settings['password'],
                $settings['options']
            );
        });
    }
}
