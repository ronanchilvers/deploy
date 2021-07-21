<?php

namespace App\Mail;

use App\Facades\Log;
use Exception;
use ReflectionClass;
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;
use Twig\Error\LoaderError;

/**
 * Mail helper
 *
 * The mail helper provides a simple interface for sending mail via swiftmailer
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Helper
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Swift_Mailer
     */
    protected $swiftMailer;

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(
        array $config,
        Swift_Mailer $swiftMailer,
        Environment $twig
    ) {
        $this->config      = $config;
        $this->swiftMailer = $swiftMailer;
        $this->twig        = $twig;
    }

    /**
     * Send an email
     *
     * @param App\Mail\Email $email
     * @return bool
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function send(Email $email)
    {
        Log::debug('Beginning mail send', [
            'email' => $email,
        ]);
        try {
            $message = new Swift_Message(
                $email->getSubject()
            );

            // Sender
            if ($email->getFrom()) {
                $message->setFrom(
                    $email->getFrom(),
                    $email->getFromName()
                );
            } else {
                $message->setFrom(
                    $this->config['from'],
                    $this->config['from_name']
                );
            }

            // Recipients
            $destinations = [
                'addTo'  => $email->getTo(),
                'addCc'  => $email->getCc(),
                'addBcc' => $email->getBcc(),
            ];
            foreach ($destinations as $setter => $addresses) {
                foreach ($addresses as $address => $name) {
                    $message->$setter($address, $name);
                }
            }

            // Default template contexts
            // foreach ($this->config['site'] as $key => $value) {
            //     $email->addTemplateContext(
            //         'site_' . $key,
            //         $value
            //     );
            // }

            // Content
            $context = $email->getTemplateContext();
            $html = $this->twig->render(
                $email->getTemplateHtml(),
                $context
            );

            try {
                $text = $this->twig->render(
                    $email->getTemplateText(),
                    $context
                );
            } catch (LoaderError $ex) {
                $text = strip_tags($html);
            }
            $text = trim($text);
            $html = trim($html);
            $message->setBody($text);
            $message->addPart($html, 'text/html');

            $failed = [];
            $result = $this->swiftMailer->send($message, $failed);
            Log::debug('Sent email', [
                'recipient_count' => $result,
                'message' => $message,
                'failed' => $failed,
            ]);
            if (0 == $result) {
                return false;
            }

            return true;
        } catch (Exception $ex) {
            Log::error($ex->getMessage(), [
                'exception' => $ex,
            ]);
            return false;
        }
    }
}
