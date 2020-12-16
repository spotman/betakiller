<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Exception\FoundHttpException;
use BetaKiller\Factory\UrlElementInstanceFactory;
use BetaKiller\Factory\UrlElementProcessorFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UrlElementRenderer implements UrlElementRendererInterface
{
    /**
     * @var \BetaKiller\Factory\UrlElementProcessorFactory
     */
    private $processorFactory;

    /**
     * @var \BetaKiller\Factory\UrlElementInstanceFactory
     */
    private $instanceFactory;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * UrlElementRenderMiddleware constructor.
     *
     * @param \BetaKiller\Factory\UrlElementProcessorFactory $processorFactory
     * @param \BetaKiller\Factory\UrlElementInstanceFactory  $instanceFactory
     * @param \BetaKiller\Url\UrlElementTreeInterface        $tree
     */
    public function __construct(
        UrlElementProcessorFactory $processorFactory,
        UrlElementInstanceFactory $instanceFactory,
        UrlElementTreeInterface $tree
    ) {
        $this->processorFactory = $processorFactory;
        $this->instanceFactory  = $instanceFactory;
        $this->tree             = $tree;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface      $urlElement
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\FoundHttpException
     */
    public function render(UrlElementInterface $urlElement, ServerRequestInterface $request): ResponseInterface
    {
        $pid = RequestProfiler::begin($request, 'UrlElement processing');

        // Use forward target if Dummy defined it
        if ($urlElement instanceof DummyModelInterface) {
            $targetCodename = $urlElement->getForwardTarget();

            if ($targetCodename) {
                $urlElement = $this->tree->getByCodename($targetCodename);
            }
        }

        $path = $request->getUri()->getPath();

        // If this is default IFace and client requested non-slash uri, redirect client to /
        if ($path !== '/' && $urlElement->isDefault() && !$urlElement->hasDynamicUrl()) {
            throw new FoundHttpException('/');
        }

        $urlProcessor = $this->processorFactory->createFromUrlElement($urlElement);
        $instance     = $this->instanceFactory->createFromUrlElement($urlElement);

        // Starting hook
        if ($instance && $instance instanceof BeforeRequestProcessingInterface) {
            $instance->beforeProcessing($request);
        }

        $response = $urlProcessor->process($instance, $request);

        // Final hook
        if ($instance && $instance instanceof AfterRequestProcessingInterface) {
            $instance->afterProcessing($request);
        }

        RequestProfiler::end($pid);

        return $response;
    }
}
