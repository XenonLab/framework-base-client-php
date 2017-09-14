<?php

namespace Xe\Framework\Client\BaseClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractClient
{
    protected $client = null;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Send an HTTP request.
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request options to apply to the given request and to the transfer
     *
     * @return ResponseInterface
     */
    protected function send(RequestInterface $request, array $options = [])
    {
        return $this->client->send($request, $options);
    }

    /**
     * Send an HTTP request.
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request options to apply to the given request and to the transfer
     *
     * @return PromiseInterface
     */
    protected function sendAsync(RequestInterface $request, array $options = [])
    {
        return $this->client->sendAsync($request, $options);
    }
}
