<?php

namespace App\Provider;

use App\Provider\AbstractProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

/**
 * Provider for gitlab repositories
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Gitlab extends AbstractProvider implements ProviderInterface
{
    /**
     * @var GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct($options)
    {
        $this->setDefaults([
            'api_uri'   => 'https://gitlab.com/api/v4/',
            'clone_uri' => 'https://oauth2:%TOKEN%@gitlab.com/%REPO%.git',
            'token'     => false,
            'timeout'   => 1.0,
        ]);
        $this->setOptions($options);

        $this->httpClient = new Client([
            'base_uri' => $this->getOption('api_uri'),
            'headers'  => [
                'PRIVATE-TOKEN' => $this->getOption('token'),
            ],
            'timeout'  => $this->getOption('timeout'),
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function exists($name)
    {
        $path     = 'projects/' . $this->encodeString($name);
        $response = $this->sendRequest($path, 'HEAD');

        return '200' == $response->getStatusCode();
    }

    /**
     * Query the API
     *
     * @return Psr\Http\Message\ResponseInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function sendRequest($path, $method = 'GET')
    {
        try {
            $request = new Request(
                $method,
                $path
            );
            $response = $this->httpClient->send(
                $request
            );

            return $response;
        } catch (ClientException $ex) {

            return $ex->getResponse();
        }
    }

    /**
     * URL encode a string
     *
     * @param string $string
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function encodeString($string)
    {
        $string = rawurlencode($string);

        return str_replace('.', '%2E', $string);
    }
}
