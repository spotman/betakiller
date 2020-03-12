<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\MissingUrlElementException;
use BetaKiller\Url\UrlElementRendererInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CustomNotFoundPageMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Url\UrlElementRendererInterface
     */
    private $renderer;

    /**
     * CustomNotFoundPageMiddleware constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface     $tree
     * @param \BetaKiller\Url\UrlElementRendererInterface $renderer
     */
    public function __construct(UrlElementTreeInterface $tree, UrlElementRendererInterface $renderer)
    {
        $this->tree     = $tree;
        $this->renderer = $renderer;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (MissingUrlElementException $e) {
            $page = $this->detectCustomPage($e);

            if (!$page) {
                throw new NotFoundHttpException();
            }

            return $this->renderer->render($page, $request);
        }
    }

    private function detectCustomPage(MissingUrlElementException $e): ?IFaceModelInterface
    {
        $parent = $e->getParentUrlElement();

        // Try to find dedicated IFace
        do {
            $layer = $parent
                ? $this->tree->getChildren($parent)
                : $this->tree->getRoot();

            foreach ($layer as $item) {
                if (!$item instanceof IFaceModelInterface) {
                    continue;
                }

                if (strpos($item->getCodename(), 'Error404') !== false) {
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
