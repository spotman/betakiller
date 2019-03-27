<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJarInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientService
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * HttpClientService constructor.
     *
     * @param \GuzzleHttp\ClientInterface               $client
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory
     */
    public function __construct(ClientInterface $client, RequestFactoryInterface $requestFactory)
    {
        $this->client         = $client;
        $this->requestFactory = $requestFactory;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function request(string $method, string $url): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $url);
    }

    public function get(string $url): RequestInterface
    {
        return $this->request('GET', $url);
    }

    public function syncCall(
        RequestInterface $request,
        CookieJarInterface $jar = null,
        array $requestOptions = null
    ): ResponseInterface {
        $options = [
            'allow_redirects' => true,
        ];

        if ($jar) {
            $options['cookies'] = $jar;
        }

        if ($requestOptions) {
            $options = \array_merge($options, $requestOptions);
        }

        return $this->client->send($request, $options);
    }
}
