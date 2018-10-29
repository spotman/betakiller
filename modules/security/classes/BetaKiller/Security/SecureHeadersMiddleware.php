<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use Aidantwoods\SecureHeaders\Http\Psr7Adapter;
use Aidantwoods\SecureHeaders\SecureHeaders;
use BetaKiller\Config\AppConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SecureHeadersMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * SecureHeadersMiddleware constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     */
    public function __construct(AppConfigInterface $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $baseUrl    = $this->appConfig->getBaseUrl();
        $baseScheme = parse_url($baseUrl, PHP_URL_SCHEME);

        if ($baseScheme !== 'https') {
            return $response;
        }

        $headers = new SecureHeaders();
        $headers->safeMode(true);
        $headers->applyOnOutput(null, false);
        $headers->hsts();
        $headers->csp('default', 'self');

        //$headers->csp('style', 'unsafe-inline');
        $headers->csp('style', $baseUrl);

        $headers->csp('script', $baseUrl);
        //$headers->csp('script', 'unsafe-inline');

        $httpAdapter = new Psr7Adapter($response);
        $headers->apply($httpAdapter);

        return $httpAdapter->getFinalResponse();
    }
}
