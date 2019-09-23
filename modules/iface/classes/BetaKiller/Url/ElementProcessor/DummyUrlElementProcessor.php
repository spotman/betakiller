<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\DummyInstance;
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

        $redirectElement = $urlHelper->detectDummyTarget($element);

        // Redirect
        return ResponseHelper::redirect($urlHelper->makeUrl($redirectElement));
    }
}
