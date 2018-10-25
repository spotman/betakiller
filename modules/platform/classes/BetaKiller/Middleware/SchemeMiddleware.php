<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Config\AppConfigInterface;
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
     * SchemeMiddleware constructor.
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
        $baseScheme = parse_url($this->appConfig->getBaseUrl(), PHP_URL_SCHEME);

        $currentUri    = $request->getUri();
        $currentScheme = $currentUri->getScheme();

        if ($baseScheme !== $currentScheme) {
            return $this->redirect($currentUri->withScheme($baseScheme));
        }

        $path = $currentUri->getPath();

        if ($path !== '/') {
            $hasSlash       = (substr($path, -1) === '/');
            $isSlashEnabled = $this->appConfig->isTrailingSlashEnabled();

            if ($hasSlash && !$isSlashEnabled) {
                return $this->redirect($currentUri->withPath(rtrim($path, '/')));
            }

            if (!$hasSlash && $isSlashEnabled) {
                return $this->redirect($currentUri->withPath($path.'/'));
            }
        }

        return $handler->handle($request);
    }

    private function redirect(UriInterface $uri): ResponseInterface
    {
        return ResponseHelper::permanentRedirect((string)$uri);
    }
}
