<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory
     * @param \Psr\Log\LoggerInterface                  $logger
     */
    public function __construct(RequestFactoryInterface $requestFactory, LoggerInterface $logger)
    {
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $logger,
                new MessageFormatter('{req_headers} => {res_headers}'),
                LogLevel::DEBUG
            ),
            Middleware::httpErrors()
        );

        $this->client = new Client([
            'handler'     => $stack,
            'http_errors' => false,
        ]);

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
