<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\MissingUrlElementException;
use BetaKiller\Url\UrlElementRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UrlElementRenderMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Url\UrlElementRendererInterface
     */
    private $renderer;

    /**
     * UrlElementRenderMiddleware constructor.
     *
     * @param \BetaKiller\Url\UrlElementRendererInterface $renderer
     */
    public function __construct(UrlElementRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Url\MissingUrlElementException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = ServerRequestHelper::getUrlContainer($request);
        $stack  = ServerRequestHelper::getUrlElementStack($request);

        if (!$stack->hasCurrent()) {
            throw new MissingUrlElementException($params, null);
        }

        $urlElement = $stack->getCurrent();

        $response = $this->renderer->render($urlElement, $request);

        $unusedParts = $params->getUnusedQueryPartsKeys();

        if ($unusedParts) {
            throw new BadRequestHttpException('Request have unused query parts: :keys', [
                ':keys' => implode(', ', $unusedParts),
            ]);
        }

        return $response;
    }
}
