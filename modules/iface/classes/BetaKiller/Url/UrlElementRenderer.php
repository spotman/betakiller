<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Exception\FoundHttpException;
use BetaKiller\Factory\UrlElementInstanceFactory;
use BetaKiller\Factory\UrlElementProcessorFactory;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UrlElementRenderer implements UrlElementRendererInterface
{
    /**
     * @var \BetaKiller\Factory\UrlElementProcessorFactory
     */
    private UrlElementProcessorFactory $processorFactory;

    /**
     * @var \BetaKiller\Factory\UrlElementInstanceFactory
     */
    private UrlElementInstanceFactory $instanceFactory;

    /**
     * UrlElementRenderMiddleware constructor.
     *
     * @param \BetaKiller\Factory\UrlElementProcessorFactory $processorFactory
     * @param \BetaKiller\Factory\UrlElementInstanceFactory  $instanceFactory
     */
    public function __construct(
        UrlElementProcessorFactory $processorFactory,
        UrlElementInstanceFactory $instanceFactory
    ) {
        $this->processorFactory = $processorFactory;
        $this->instanceFactory  = $instanceFactory;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface      $urlElement
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function render(UrlElementInterface $urlElement, ServerRequestInterface $request): ResponseInterface
    {
        $pid = RequestProfiler::begin($request, 'UrlElement processing');

        // Use forward target for Dummy
        if ($urlElement instanceof DummyModelInterface) {
            $urlHelper = ServerRequestHelper::getUrlHelper($request);

            // Check redirect
            $redirectTarget = $urlHelper->detectDummyRedirectTarget($urlElement);

            if ($redirectTarget) {
                $redirectTo = $urlHelper->makeUrl($redirectTarget);

                return ResponseHelper::redirect($redirectTo);
            }

            // Check forwarding
            $forwardTarget = $urlHelper->detectDummyForwardTarget($urlElement);

            if ($forwardTarget) {
                $urlElement = $forwardTarget;
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
        if ($instance instanceof BeforeRequestProcessingInterface) {
            $instance->beforeProcessing($request);
        }

        $response = $urlProcessor->process($instance, $request);

        // Final hook
        if ($instance instanceof AfterRequestProcessingInterface) {
            $instance->afterProcessing($request);
        }

        RequestProfiler::end($pid);

        return $response;
    }
}
