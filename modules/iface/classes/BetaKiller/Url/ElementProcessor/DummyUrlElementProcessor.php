<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\DummyInstance;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementInstanceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Dummy URL element processor
 */
class DummyUrlElementProcessor implements UrlElementProcessorInterface
{
    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInstanceInterface $instance
     * @param \Psr\Http\Message\ServerRequestInterface    $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementProcessorException
     * @throws \BetaKiller\Url\UrlElementException
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

        $redirectElement = $urlHelper->detectDummyRedirectTarget($element);

        // Redirect if defined
        if ($redirectElement) {
            return ResponseHelper::redirect($urlHelper->makeUrl($redirectElement));
        }

        // Fallback to HTTP 404
        throw new NotFoundHttpException();
    }
}
