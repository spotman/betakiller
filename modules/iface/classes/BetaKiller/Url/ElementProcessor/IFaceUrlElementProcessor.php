<?php

namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInstanceInterface;
use BetaKiller\View\IFaceRendererInterface;
use DateTimeImmutable;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * IFace URL element processor
 */
readonly class IFaceUrlElementProcessor implements UrlElementProcessorInterface
{
    public function __construct(
        private IFaceRendererInterface $renderer,
        private UrlElementHelper $elementHelper,
        private IFaceCache $ifaceCache
    ) {
    }

    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInstanceInterface $iface
     * @param \Psr\Http\Message\ServerRequestInterface    $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementProcessorException
     * @throws \PageCache\PageCacheException
     * @throws \Throwable
     */
    public function process(
        UrlElementInstanceInterface $iface,
        ServerRequestInterface $request
    ): ResponseInterface {
        if (!$iface instanceof IFaceInterface) {
            throw new UrlElementProcessorException('Instance must be :must, but :real provided', [
                ':real' => get_class($iface),
                ':must' => IFaceInterface::class,
            ]);
        }

        $model = $this->elementHelper->getInstanceModel($iface);

        if (!$model instanceof IFaceModelInterface) {
            throw new LogicException();
        }

        $urlContainer = ServerRequestHelper::getUrlContainer($request);
        $user         = ServerRequestHelper::getUser($request);

        // Processing page cache for quests if no URL query parameters (skip caching for authorized users)
        if (!$urlContainer->getQueryPartsKeys() && $user->isGuest()) {
            $this->ifaceCache->process($iface, $request);
        }

        try {
            $output = $this->renderer->render($iface, $request);

            $response = ResponseHelper::html($output);

            return $model->isCacheEnabled()
                ? ResponseHelper::enableCaching($response, new DateTimeImmutable('now'), $model->getExpiresInterval())
                : ResponseHelper::disableCaching($response);
        } catch (Throwable $e) {
            // Prevent response caching
            $this->ifaceCache->disable();
            throw $e;
        }
    }
}
