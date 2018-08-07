<?php
namespace BetaKiller\Url\ElementProcessor;

use \BetaKiller\Config\AppConfigInterface;
use \BetaKiller\Exception\BadRequestHttpException;
use \BetaKiller\Exception\FoundHttpException;
use \BetaKiller\Exception\PermanentRedirectHttpException;
use \BetaKiller\Factory\IFaceFactory;
use \BetaKiller\IFace\Cache\IFaceCache;
use \BetaKiller\IFace\IFaceInterface;
use \BetaKiller\Model\UserInterface;
use \BetaKiller\Url\Container\UrlContainerInterface;
use \BetaKiller\Url\UrlElementInterface;
use \BetaKiller\Url\IFaceModelInterface;
use \BetaKiller\View\IFaceView;

/**
 * IFace URL element processor
 */
class IFaceUrlElementProcessor extends UrlElementProcessorAbstract
{
    /**
     * Application config
     *
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

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
     * User controller
     *
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @param \BetaKiller\Config\AppConfigInterface           $appConfig
     * @param \BetaKiller\Factory\IFaceFactory                $ifaceFactory
     * @param \BetaKiller\View\IFaceView                      $ifaceView
     * @param \BetaKiller\IFace\Cache\IFaceCache              $ifaceCache
     * @param \BetaKiller\Model\UserInterface                 $user
     */
    public function __construct(
        AppConfigInterface $appConfig,
        IFaceFactory $ifaceFactory,
        IFaceView $ifaceView,
        IFaceCache $ifaceCache,
        UserInterface $user
    ) {
        $this->appConfig    = $appConfig;
        $this->ifaceFactory = $ifaceFactory;
        $this->ifaceView    = $ifaceView;
        $this->ifaceCache   = $ifaceCache;
        $this->user         = $user;
    }

    /**
     * Execute processing on URL element
     * 
     * @param \BetaKiller\Url\UrlElementInterface                  $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $urlContainer [optional]
     * @param \Response|null                                       $response     [optional]
     *
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Exception\PermanentRedirectHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementProcessorException
     * @throws \PageCache\PageCacheException
     * @throws \Throwable
     */
    public function process(
        UrlElementInterface $model,
        ?UrlContainerInterface $urlContainer = null,
        ?\Response $response = null
    ): void
    {
        if (!($model instanceof IFaceModelInterface)) {
            throw new UrlElementProcessorException('Invalid model :class_invalid. Model must be :class_valid', [
                ':class_invalid' => \get_class($model),
                ':class_valid'   => UrlElementInterface::class,
            ]);
        }
        if (!$urlContainer) {
            throw new UrlElementProcessorException('URL container must be defined');
        }
        if (!$response) {
            throw new UrlElementProcessorException('Response controller must be defined');
        }

        // If this is default IFace and client requested non-slash uri, redirect client to /
        $path = parse_url($this->request->url(), PHP_URL_PATH);
        if ($path !== '/' && $model->isDefault() && !$model->hasDynamicUrl()) {
            throw new FoundHttpException('/');
        }
        if ($path !== '/') {
            $hasTrailingSlash       = (substr($path, -1) === '/');
            $isTrailingSlashEnabled = $this->appConfig->isTrailingSlashEnabled();

            if ($hasTrailingSlash && !$isTrailingSlashEnabled) {
                throw new PermanentRedirectHttpException(rtrim($path, '/'));
            }

            if (!$hasTrailingSlash && $isTrailingSlashEnabled) {
                throw new PermanentRedirectHttpException($path.'/');
            }
        }

        //
        $iface = $this->ifaceFactory->createFromUrlElement($model);

        // Starting hook
        $iface->before();

        //
        $urlContainer->setQueryParts($this->request->query());

        // Processing page cache if no URL query parameters
        if (!$urlContainer->getQueryPartsKeys()) {
            $this->processIFaceCache($iface);
        }

        try {
            $output = $this->ifaceView->render($iface);

            // Final hook
            $iface->after();

            $unusedParts = $urlContainer->getUnusedQueryPartsKeys();
            if ($unusedParts) {
                throw new BadRequestHttpException('Request have unused query parts: :keys', [
                    ':keys' => implode(', ', $unusedParts),
                ]);
            }

            $response->last_modified($iface->getLastModified());
            $response->expires($iface->getExpiresDateTime());
            $response->send_string($output);
        } catch (\Throwable $e) {
            // Prevent response caching
            $this->ifaceCache->disable();
            throw $e;
        }
    }


    /**
     * Cashing IFace element
     *
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @throws \PageCache\PageCacheException
     */
    private function processIFaceCache(IFaceInterface $iface): void
    {
        // Skip caching if request method is not GET nor HEAD
        if (!\in_array($this->request->method(), ['GET', 'HEAD'], true)) {
            return;
        }

        // Skip caching for authorized users
        if (!$this->user->isGuest()) {
            return;
        }

        $this->ifaceCache->process($iface);
    }
}
