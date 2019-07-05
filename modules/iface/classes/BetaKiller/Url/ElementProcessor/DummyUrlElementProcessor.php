<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Url\DummyInstance;
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
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInstanceInterface $instance
     * @param \Psr\Http\Message\ServerRequestInterface    $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(UrlElementInstanceInterface $instance, ServerRequestInterface $request): ResponseInterface
    {
        if (!$instance instanceof DummyInstance) {
            throw new UrlElementProcessorException('Instance must be :must, but :real provided', [
                ':real' => get_class($instance),
                ':must' => DummyInstance::class,
            ]);
        }

        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $element   = $instance->getModel();

        $redirectElement = $element;

        // Process chained dummies to prevent multiple redirects in browser
        do {
            $redirectTarget = $redirectElement->getRedirectTarget();

            $redirectElement = $redirectTarget
                ? $urlHelper->getUrlElementByCodename($redirectTarget)
                : null;
        } while ($redirectElement && $redirectElement instanceof DummyModelInterface && $redirectElement->getRedirectTarget());

        // Fallback to parent if redirect is not defined
        if (!$redirectElement) {
            $redirectElement = $this->getParentIFace($element);
        }

        // Redirect
        return ResponseHelper::redirect($urlHelper->makeUrl($redirectElement));
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
