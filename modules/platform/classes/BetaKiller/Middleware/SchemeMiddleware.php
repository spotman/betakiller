<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SchemeMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private AppConfigInterface $appConfig;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * SchemeMiddleware constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Env\AppEnvInterface       $appEnv
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
        if ($this->appEnv->isInternalWebServer()) {
            // Forward processing
            return $handler->handle($request);
        }

        $p = RequestProfiler::begin($request, 'SchemeMiddleware');

        $baseUri    = $this->appConfig->getBaseUri();
        $baseScheme = $baseUri->getScheme();
        $baseHost   = $baseUri->getHost();

        $currentUri    = $request->getUri();
        $currentScheme = $currentUri->getScheme();
        $currentHost   = $currentUri->getHost();

        if ($baseScheme !== $currentScheme) {
            return $this->redirect($currentUri->withScheme($baseScheme));
        }

        // Skip domain check in development mode
        if ($baseHost !== $currentHost && !$this->appEnv->inDevelopmentMode()) {
            return $this->redirect($currentUri->withHost($baseHost));
        }

        $path = $currentUri->getPath();
        $file = \basename($path);

        if ($path !== '/' && !str_contains($file, '.')) {
            $hasSlash       = (str_ends_with($path, '/'));
            $isSlashEnabled = $this->appConfig->isTrailingSlashEnabled();

            if ($hasSlash && !$isSlashEnabled) {
                return $this->redirect($currentUri->withPath(rtrim($path, '/')));
            }

            if (!$hasSlash && $isSlashEnabled) {
                return $this->redirect($currentUri->withPath($path.'/'));
            }
        }

        // Fetch ignored query params to prevent exceptions
        $ignoredParams = $this->appConfig->getIgnoredQueryParams();

        if ($ignoredParams) {
            $request = ServerRequestHelper::removeQueryParams($request, $ignoredParams);
        }

        RequestProfiler::end($p);

        // Forward processing
        return $handler->handle($request);
    }

    private function redirect(UriInterface $uri): ResponseInterface
    {
        // Keep POST data on redirect
        // @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection
        return ResponseHelper::temporaryRedirect((string)$uri);
    }
}
