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
     * @var \BetaKiller\Security\SecurityConfigInterface
     */
    private $securityConfig;

    /**
     * SecureHeadersMiddleware constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface        $appConfig
     * @param \BetaKiller\Security\SecurityConfigInterface $securityConfig
     */
    public function __construct(AppConfigInterface $appConfig, SecurityConfigInterface $securityConfig)
    {
        $this->appConfig      = $appConfig;
        $this->securityConfig = $securityConfig;
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

        // Do not add headers
        $headers->auto(SecureHeaders::AUTO_ALL);

        // Report URI first
        $reportUri = (string)$baseUri->withPath(CspReportHandler::URL);
        $headers->csp('report', $reportUri);
        $headers->csp('report', $reportUri, true);

        if ($this->securityConfig->isCspSafeModeEnabled()) {
            $headers->safeMode(true);
        }

        if ($this->securityConfig->isHstsEnabled()) {
            // Basic STS headers with safe mode enabled to prevent long-lasting effects of incorrect configuration
            $headers->hsts(
                $this->securityConfig->getHstsMaxAge(),
                $this->securityConfig->isHstsForSubdomains(),
                $this->securityConfig->isHstsPreload()
            );
        }

        // Enable/disable errors logging
        $headers->errorReporting($this->securityConfig->isErrorLogEnabled());

        $headers->csp('default', $baseUrl);
        $headers->csp('image', $baseUrl);
        $headers->csp('style', $baseUrl);
        $headers->csp('script', $baseUrl);
        $headers->csp('font', $baseUrl);

        // @see https://www.w3.org/TR/CSP3/#grammardef-report-sample
        $headers->csp('script', "'report-sample'");
        $headers->csp('style', "'report-sample'");
        $headers->csp('font', "'report-sample'");
        $headers->csp('image', "'report-sample'");
        $headers->csp('connect', "'report-sample'");

        $headers->csp('connect', $baseUrl);
        $headers->csp('connect', 'wss://'.$baseUri->getHost()); // For secure Websocket

        foreach ($this->securityConfig->getCspRules() as $ruleName => $ruleValues) {
            foreach ($ruleValues as $value) {
                $headers->csp($ruleName, $value);
            }
        }

        $response = $handler->handle($request->withAttribute(SecureHeaders::class, $headers));

        if (!$this->securityConfig->isCspEnabled()) {
            return $response;
        }

        foreach ($this->securityConfig->getHeadersToAdd() as $headerName => $headerValue) {
            $response = $response->withHeader($headerName, $headerValue);
        }

        foreach ($this->securityConfig->getHeadersToRemove() as $headerName) {
            $response = $response->withoutHeader($headerName);
        }

        $httpAdapter = new Psr7Adapter($response);
        $headers->apply($httpAdapter);

        return $httpAdapter->getFinalResponse();
    }
}
