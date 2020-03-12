<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Factory\IFaceFactory;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Url\UrlElementInstanceInterface;
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

        $urlContainer = ServerRequestHelper::getUrlContainer($request);
        $user         = ServerRequestHelper::getUser($request);

        // Processing page cache for quests if no URL query parameters (skip caching for authorized users)
        if (!$urlContainer->getQueryPartsKeys() && $user->isGuest()) {
            $this->ifaceCache->process($iface, $request);
        }

        try {
            $output = $this->ifaceView->render($iface, $request);

            $response = ResponseHelper::html($output);
            $response = ResponseHelper::setExpires($response, $iface->getExpiresDateTime());

            if ($iface->isHttpCachingEnabled()) {
                $maxAge = $iface->getExpiresSeconds();

                $response = ResponseHelper::setLastModified($response, $iface->getLastModified());
                $response = ResponseHelper::setCacheControl($response, 'private, must-revalidate, max-age='.$maxAge);
            } else {
                $response = ResponseHelper::setCacheControl($response, 'no-cache, no-store, must-revalidate');
                $response = ResponseHelper::setPragmaNoCache($response);
            }
        } catch (Throwable $e) {
            // Prevent response caching
            $this->ifaceCache->disable();
            throw $e;
        }

        return $response;
    }
}
