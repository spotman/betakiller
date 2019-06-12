<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Factory\IFaceFactory;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInstanceInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\View\IFaceView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * IFace URL element processor
 */
class IFaceUrlElementProcessor implements UrlElementProcessorInterface
{
    /**
     * IFace Factory
     *
     * @var \BetaKiller\Factory\IFaceFactory
     */
    private $ifaceFactory;

    /**
     * Templates controller
     *
     * @var \BetaKiller\View\IFaceView
     */
    private $ifaceView;

    /**
     * Cache manager of IFace elements
     *
     * @var \BetaKiller\IFace\Cache\IFaceCache
     */
    private $ifaceCache;

    /**
     * @param \BetaKiller\Factory\IFaceFactory   $ifaceFactory
     * @param \BetaKiller\View\IFaceView         $ifaceView
     * @param \BetaKiller\IFace\Cache\IFaceCache $ifaceCache
     */
    public function __construct(
        IFaceFactory $ifaceFactory,
        IFaceView $ifaceView,
        IFaceCache $ifaceCache
    ) {
        $this->ifaceFactory = $ifaceFactory;
        $this->ifaceView    = $ifaceView;
        $this->ifaceCache   = $ifaceCache;
    }

    /**
     * Create UrlElement instance for provided model
     *
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     */
    public function createInstance(UrlElementInterface $model): ?UrlElementInstanceInterface
    {
        if (!$model instanceof IFaceModelInterface) {
            throw new UrlElementProcessorException('Model must instance of :must but :real provided', [
                ':real' => \get_class($model),
                ':must' => IFaceModelInterface::class,
            ]);
        }

        return $this->ifaceFactory->createFromUrlElement($model);
    }

    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface      $model
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementProcessorException
     * @throws \PageCache\PageCacheException
     * @throws \Throwable
     */
    public function process(
        UrlElementInterface $model,
        ServerRequestInterface $request
    ): ResponseInterface {
        if (!$model instanceof IFaceModelInterface) {
            throw new UrlElementProcessorException('Model must instance of :must but :real provided', [
                ':real' => \get_class($model),
                ':must' => IFaceModelInterface::class,
            ]);
        }

        $urlContainer = ServerRequestHelper::getUrlContainer($request);
        $user         = ServerRequestHelper::getUser($request);

        // Create current IFace instance
        $iface = $this->createInstance($model);

        // Processing page cache for quests if no URL query parameters (skip caching for authorized users)
        if (!$urlContainer->getQueryPartsKeys() && $user->isGuest()) {
            $this->processIFaceCache($iface, $request);
        }

        try {
            $output = $this->ifaceView->render($iface, $request);

            // TODO Apply to other UrlElement types (Action, WebHook)
            $unusedParts = $urlContainer->getUnusedQueryPartsKeys();
            if ($unusedParts) {
                throw new BadRequestHttpException('Request have unused query parts: :keys', [
                    ':keys' => implode(', ', $unusedParts),
                ]);
            }

            $response = ResponseHelper::html($output);
            $response = ResponseHelper::setLastModified($response, $iface->getLastModified());
            $response = ResponseHelper::setExpires($response, $iface->getExpiresDateTime());
        } catch (Throwable $e) {
            // Prevent response caching
            $this->ifaceCache->disable();
            throw $e;
        }

        return $response;
    }

    /**
     * Cashing IFace element
     *
     * @param \BetaKiller\IFace\IFaceInterface         $iface
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \PageCache\PageCacheException
     */
    private function processIFaceCache(IFaceInterface $iface, ServerRequestInterface $request): void
    {
        // Skip caching if request method is not GET nor HEAD
        if (!\in_array(\mb_strtoupper($request->getMethod()), ['GET', 'HEAD'], true)) {
            return;
        }

        $this->ifaceCache->process($iface, $request);
    }
}
