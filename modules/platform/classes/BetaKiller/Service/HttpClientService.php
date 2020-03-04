<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJarInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientService
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * HttpClientService constructor.
     *
     * @param \GuzzleHttp\ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function syncGet(
        string $url,
        CookieJarInterface $jar = null,
        array $requestOptions = null
    ): ResponseInterface {
        return $this->syncRequest('get', $url, $jar, $requestOptions);
    }

    public function syncRequest(
        string $method,
        string $url,
        CookieJarInterface $jar = null,
        array $requestOptions = null
    ): ResponseInterface {
        return $this->client->request($method, $url, $this->makeRequestOptions($jar, $requestOptions));
    }

    private function makeRequestOptions(CookieJarInterface $jar = null, array $requestOptions = null): array
    {
        $options = [
            'allow_redirects' => true,
        ];

        if ($jar !== null) {
            $options['cookies'] = $jar;
        }

        if ($requestOptions) {
            $options = \array_merge($options, $requestOptions);
        }

        return $options;
    }
}
