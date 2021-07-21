<?php

namespace App\Mail;

use App\Mail\Helper;
use Ronanchilvers\Container\Container;
use Ronanchilvers\Container\ServiceProviderInterface;
use Slim\Views\Twig;
use Swift_Mailer;
use Swift_SmtpTransport;

/**
 * Provider for mail services
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class MailProvider implements ServiceProviderInterface
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $container)
    {
        $container->set('mailer_settings', []);

        $container->set('swift_mailer', function ($c) {
            $config = $c->get('mailer_settings')['transport'];
            $transport = new Swift_SmtpTransport(
                $config['host'],
                $config['port']
            );
            if (isset($config['username'], $config['password'])) {
                $transport->setUsername(
                    $config['username']
                );
                $transport->setPassword(
                    $config['password']
                );
            }
            if (isset($config['tls']) && true == $config['tls']) {
                $transport->setEncryption(
                    'tls'
                );
            }

            return new Swift_Mailer(
                $transport
            );
        });
        $container->set(Helper::class, function ($c) {
            $config      = $c->get('mailer_settings')['options'];
            $swiftMailer = $c->get('swift_mailer');
            $twig        = $c->get(Twig::class)->getEnvironment();

            return new Helper(
                $config,
                $swiftMailer,
                $twig
            );
        });
    }
}
