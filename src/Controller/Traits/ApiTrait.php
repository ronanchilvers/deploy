<?php

namespace App\Controller\Traits;

use Psr\Http\Message\ResponseInterface;

/**
 * Utility trait for API controllers
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait ApiTrait
{
    /**
     * Render an API response
     *
     * @param ResponseInterface $response
     * @param array $data
     * @return ResponseInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function apiResponse(
        ResponseInterface $response,
        array $data
    ): ResponseInterface {
        return $this->renderJson(
            $response,
            'ok',
            $data
        );
    }

    /**
     * Render an API error response
     *
     * @param ResponseInterface $response
     * @param string $message
     * @param integer $code The HTTP response code to use
     * @return ResponseInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function apiError(
        ResponseInterface $response,
        string $message,
        int $code = null
    ): ResponseInterface {
        if (is_null($code) || 0 == $code) {
            $code = 400;
        }
        $data = ['message' => $message];
        return $this->renderJson(
            $response,
            'error',
            $data
        )->withStatus($code);
    }

    /**
     * Render a json response
     *
     * @param ResponseInterface $response
     * @param array $data
     * @return ResponseInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function renderJson(
        ResponseInterface $response,
        string $status,
        array $data
    ): ResponseInterface {
        $data = [
            'status' => $status,
            'data' => $data,
        ];

        return $response->withJson($data);
    }
}
