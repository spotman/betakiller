<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception\FoundHttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

        $currentUri = $request->getUri();
        $currentScheme = $currentUri->getScheme();

        if ($baseScheme !== $currentScheme) {
            $redirectUrl = (string)$currentUri->withScheme($baseScheme);

            throw new FoundHttpException($redirectUrl);
        }

        return $handler->handle($request);
    }
}
