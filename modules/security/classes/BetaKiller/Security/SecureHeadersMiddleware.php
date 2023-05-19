<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use Aidantwoods\SecureHeaders\Http\Psr7Adapter;
use Aidantwoods\SecureHeaders\SecureHeaders;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Dev\RequestProfiler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * SecureHeadersMiddleware constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface        $appConfig
     * @param \BetaKiller\Security\SecurityConfigInterface $securityConfig
     * @param \Psr\Log\LoggerInterface                     $logger
     */
    public function __construct(
        AppConfigInterface      $appConfig,
        SecurityConfigInterface $securityConfig,
        LoggerInterface         $logger
    ) {
        $this->appConfig      = $appConfig;
        $this->securityConfig = $securityConfig;
        $this->logger         = $logger;
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
        RequestProfiler::mark($request, 'SecureHeadersMiddleware started');

        if (!$this->appConfig->isSecure() || !$this->securityConfig->isCspEnabled()) {
            return $handler->handle($request);
        }

        $headers = new SecureHeaders();

        // Do not add headers
        $headers->auto(SecureHeaders::AUTO_ALL);

        $this->configureCsp($headers);

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

        foreach ($this->securityConfig->getProtectedCookies() as $protectedCookieName) {
            $headers->protectedCookie($protectedCookieName, $headers::COOKIE_REMOVE | $headers::COOKIE_NAME);
        }

//        $headers->csp('default', $baseUrl);
//        $headers->csp('image', $baseUrl);
//        $headers->csp('style', $baseUrl);
//        $headers->csp('script', $baseUrl);
//        $headers->csp('font', $baseUrl);
//
//        $headers->csp('connect', $baseUrl);
//        $headers->csp('connect', 'wss://'.$baseUri->getHost());

        $response = $handler->handle($request->withAttribute(SecureHeaders::class, $headers));

        foreach ($this->securityConfig->getHeadersToAdd() as $headerName => $headerValue) {
            if ($response->hasHeader($headerName)) {
                $this->logger->warning('Duplicate HTTP header ":name"', [
                    ':name' => $headerName,
                ]);
            }

            $response = $response->withHeader($headerName, $headerValue);
        }

        foreach ($this->securityConfig->getHeadersToRemove() as $headerName) {
            $response = $response->withoutHeader($headerName);
        }

        $httpAdapter = new Psr7Adapter($response);
        $headers->apply($httpAdapter);

        return $httpAdapter->getFinalResponse();
    }

    private function configureCsp(SecureHeaders $headers): void
    {
        $baseUri = $this->appConfig->getBaseUri();

        if ($this->securityConfig->isCspSafeModeEnabled()) {
            $headers->safeMode(true);
        }

        if ($this->securityConfig->isCspReportEnabled()) {
            // Report URI first
            $reportUri = (string)$baseUri->withPath(CspReportHandler::URL);
            $headers->csp('report', $reportUri);
            $headers->cspro('report', $reportUri);
        }

        foreach ($this->securityConfig->getCspRules() as $ruleName => $ruleValues) {
            foreach ($ruleValues as $value) {
                $headers->csp($ruleName, $value);
            }
        }
    }
}
