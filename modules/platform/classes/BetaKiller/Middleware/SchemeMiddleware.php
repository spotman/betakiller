<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\ResponseHelper;
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
    private $appConfig;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * SchemeMiddleware constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Helper\AppEnvInterface    $appEnv
     */
    public function __construct(AppConfigInterface $appConfig, AppEnvInterface $appEnv)
    {
        $this->appConfig = $appConfig;
        $this->appEnv = $appEnv;
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

        $baseScheme = $this->appConfig->getBaseUri()->getScheme();

        $currentUri    = $request->getUri();
        $currentScheme = $currentUri->getScheme();

        if ($baseScheme !== $currentScheme) {
            return $this->redirect($currentUri->withScheme($baseScheme));
        }

        $path = $currentUri->getPath();
        $file = \basename($path);

        if ($path !== '/' && \strpos($file, '.') === false) {
            $hasSlash       = (substr($path, -1) === '/');
            $isSlashEnabled = $this->appConfig->isTrailingSlashEnabled();

            if ($hasSlash && !$isSlashEnabled) {
                return $this->redirect($currentUri->withPath(rtrim($path, '/')));
            }

            if (!$hasSlash && $isSlashEnabled) {
                return $this->redirect($currentUri->withPath($path.'/'));
            }
        }

        // Forward processing
        return $handler->handle($request);
    }

    private function redirect(UriInterface $uri): ResponseInterface
    {
        return ResponseHelper::permanentRedirect((string)$uri);
    }
}
