<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use Aidantwoods\SecureHeaders\Http\Psr7Adapter;
use Aidantwoods\SecureHeaders\SecureHeaders;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Helper\AppEnvInterface;
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
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * SecureHeadersMiddleware constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Helper\AppEnvInterface    $appEnv
     */
    public function __construct(AppConfigInterface $appConfig, AppEnvInterface $appEnv)
    {
        $this->appConfig = $appConfig;
        $this->appEnv    = $appEnv;
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
        $headers->safeMode(true);
        $headers->applyOnOutput(null, false);

        // Report URI first
        $reportUri = (string)$baseUri->withPath(CspReportHandler::URL);
        $headers->csp('report-uri', $reportUri);
        $headers->csp('report-uri', $reportUri, true);

        $headers->hsts();
        $headers->csp('default', $baseUrl);

        $headers->csp('image', $baseUrl);
        $headers->csp('style', $baseUrl);
        $headers->csp('script', $baseUrl);

        if ($this->appEnv->isDebugEnabled()) {
            // DebugBar uses inline tags and images
            $headers->csp('image', 'data:');
            $headers->csp('style', 'unsafe-inline');
            $headers->csp('script', 'unsafe-inline');
            $headers->csp('script', 'unsafe-eval');
        }

        // TODO Inject this nonce in Request and use in StaticAssets
//        $styleNonce  = $headers->cspNonce('style');
//        $scriptNonce = $headers->cspNonce('script');

        $response = $handler->handle($request);

        $httpAdapter = new Psr7Adapter($response);
        $headers->apply($httpAdapter);

        return $httpAdapter->getFinalResponse();
    }
}
