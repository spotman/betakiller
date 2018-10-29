<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use GuzzleHttp\Client;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientService
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * HttpClientService constructor.
     *
     */
    public function __construct(RequestFactoryInterface $requestFactory)
    {
        $this->client = new Client();
        $this->requestFactory = $requestFactory;
    }

    public function request(string $method, string $url): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $url);
    }

    public function get(string $url): RequestInterface
    {
        return $this->request('GET', $url);
    }

    public function syncCall(RequestInterface $request, array $requestOptions = null): ResponseInterface
    {
        $options = [
            'allow_redirects' => true,
        ];

        if ($requestOptions) {
            $options = \array_merge($options, $requestOptions);
        }

        return $this->client->send($request, $options);
    }
}
