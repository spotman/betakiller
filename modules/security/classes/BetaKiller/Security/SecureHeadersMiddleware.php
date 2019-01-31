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
        if (!$this->appConfig->isSecure()) {
            return $handler->handle($request);
        }

        $baseUri = $this->appConfig->getBaseUri();
        $baseUrl = (string)$baseUri;

        $headers = new SecureHeaders();
        $headers->applyOnOutput(null, false);

        // Do not add headers
        $headers->auto(SecureHeaders::AUTO_ALL & ~(SecureHeaders::AUTO_ADD | SecureHeaders::AUTO_COOKIE_HTTPONLY));

        // Report URI first
        $reportUri = (string)$baseUri->withPath(CspReportHandler::URL);
        $headers->csp('report', $reportUri);
        $headers->csp('report', $reportUri, true);

        // Basic STS headers with safe mode enabled to prevent long-lasting effects of incorrect configuration
        $headers->hsts(3600, false, false);
        $headers->safeMode(true);

        $headers->csp('default', $baseUrl);
        $headers->csp('image', $baseUrl);
        $headers->csp('style', $baseUrl);
        $headers->csp('script', $baseUrl);
        $headers->csp('font', $baseUrl);

        // @see https://www.w3.org/TR/CSP3/#grammardef-report-sample
        $headers->csp('script', 'report-sample');
        $headers->csp('style', 'report-sample');
        $headers->csp('font', 'report-sample');

        $headers->csp('connect', $baseUrl);
        $headers->csp('connect', 'wss://'.$baseUri->getHost()); // For secure Websocket

        $response = $handler->handle($request->withAttribute(SecureHeaders::class, $headers));

        $httpAdapter = new Psr7Adapter($response);
        $headers->apply($httpAdapter);

        return $httpAdapter->getFinalResponse();
    }
}
