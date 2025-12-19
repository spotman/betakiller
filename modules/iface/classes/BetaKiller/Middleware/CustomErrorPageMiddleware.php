<?php

declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\EntityNotAllowedException;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\MissingUrlElementException;
use BetaKiller\Url\UrlElementNotAllowedException;
use BetaKiller\Url\UrlElementRendererInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class CustomErrorPageMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UrlElementTreeInterface $tree,
        private UrlElementRendererInterface $renderer,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (MissingUrlElementException|UrlElementNotAllowedException|EntityNotAllowedException $e) {
            $page = $this->detectCustomPage($e);

            if ($page) {
                // Push this page to UrlElementStack to make "inertia" module happy
                // @see DefaultInertiaTemplateContextFactory
                ServerRequestHelper::getUrlElementStack($request)->push($page);

                return $this->renderer->render($page, $request);
            }

            throw match (true) {
                $e instanceof UrlElementNotAllowedException, $e instanceof EntityNotAllowedException => new AccessDeniedException(
                    $e->getMessage(),
                    null,
                    $e
                ),
                $e instanceof MissingUrlElementException => new NotFoundHttpException(null, null, $e)
            };
        }
    }

    private function detectCustomPage(MissingUrlElementException|UrlElementNotAllowedException|EntityNotAllowedException $e): ?IFaceModelInterface
    {
        $parent = match (true) {
            $e instanceof MissingUrlElementException => $e->getParentUrlElement(),
            $e instanceof UrlElementNotAllowedException => $e->getUrlElement(),
            $e instanceof EntityNotAllowedException => $e->getUrlElement(),
        };

        $keyCode = match (true) {
            $e instanceof MissingUrlElementException => 'Error404',
            $e instanceof UrlElementNotAllowedException, $e instanceof EntityNotAllowedException => 'Error403',
        };

        // Try to find dedicated IFace
        do {
            $layer = $parent
                ? $this->tree->getChildren($parent)
                : $this->tree->getRoot();

            foreach ($layer as $item) {
                if (!$item instanceof IFaceModelInterface) {
                    continue;
                }

                if (str_contains($item->getCodename(), $keyCode)) {
                    return $item;
                }
            }

            if ($parent) {
                $parent = $this->tree->getParent($parent);
            }
        } while ($parent);

        return null;
    }
}
