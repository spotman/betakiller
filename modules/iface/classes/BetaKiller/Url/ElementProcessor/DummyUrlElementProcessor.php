<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInstanceInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Dummy URL element processor
 */
class DummyUrlElementProcessor implements UrlElementProcessorInterface
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * DummyUrlElementProcessor constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     */
    public function __construct(UrlElementTreeInterface $tree)
    {
        $this->tree = $tree;
    }

    /**
     * Create UrlElement instance for provided model
     *
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \BetaKiller\Url\UrlElementInstanceInterface
     */
    public function createInstance(UrlElementInterface $model): ?UrlElementInstanceInterface
    {
        // No instance for Dummies
        return null;
    }

    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface      $model
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(UrlElementInterface $model, ServerRequestInterface $request): ResponseInterface
    {
        if (!$model instanceof DummyModelInterface) {
            throw new UrlElementProcessorException('Model must be instance of :must, but :real provided', [
                ':real' => \get_class($model),
                ':must' => DummyModelInterface::class,
            ]);
        }

        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $parent = $this->getParentIFace($model);

        // Redirect
        return ResponseHelper::redirect($urlHelper->makeUrl($parent));
    }

    private function getParentIFace(UrlElementInterface $model): UrlElementInterface
    {
        // Find nearest IFace
        foreach ($this->tree->getReverseBreadcrumbsIterator($model) as $parent) {
            if ($parent instanceof IFaceModelInterface) {
                return $parent;
            }
        }

        $parent = $this->tree->getParent($model);

        // Redirect root dummies to default element
        if (!$parent) {
            return $this->tree->getDefault();
        }

        throw new UrlElementException('No IFace found for Dummy with URI ":uri" and parent ":parent"', [
            ':uri'    => $model->getUri(),
            ':parent' => $model->getParentCodename() ?: 'root',
        ]);
    }
}
