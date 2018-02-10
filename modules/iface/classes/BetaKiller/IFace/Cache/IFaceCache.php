<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\IFace\IFaceInterface;
use PageCache\PageCache;
use Psr\Log\LoggerInterface;

class IFaceCache
{
    /**
     * @var PageCache
     */
    protected $pageCache;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var \BetaKiller\IFace\Cache\IFacePageCacheStrategy
     */
    private $strategy;

    /**
     * IFaceCache constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface          $config
     * @param \BetaKiller\Helper\AppEnvInterface             $appEnv
     * @param \PageCache\PageCache                           $pageCache
     * @param \BetaKiller\IFace\Cache\IFacePageCacheStrategy $strategy
     * @param \Psr\Log\LoggerInterface                       $logger
     *
     * @throws \PageCache\PageCacheException
     */
    public function __construct(
        AppConfigInterface $config,
        AppEnvInterface $appEnv,
        PageCache $pageCache,
        IFacePageCacheStrategy $strategy,
        LoggerInterface $logger
    ) {
        $this->enabled = !$appEnv->isCLI() && $config->isPageCacheEnabled();

        $this->pageCache = $pageCache;
        $this->strategy  = $strategy;

        $this->pageCache->config()
            ->setCachePath($config->getPageCachePath())
            ->setEnableLog(true)
            ->setSendHeaders(true)
            ->setForwardHeaders(true);

        $this->pageCache->setLogger($logger);
    }

    /**
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function clearModelCache(): void
    {
        // deal with child ifaces (clear cache for whole branch)

        throw new NotImplementedHttpException();
    }

    public function clearCache(): void
    {
        $this->pageCache->clearAllCache();
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @throws \PageCache\PageCacheException
     */
    public function process(IFaceInterface $iface): void
    {
        if (!$this->enabled) {
            return;
        }

        $expires = $iface->getExpiresSeconds();

        // Skip caching if content expired already (admin interfaces and non-cachable pages)
        if ($expires < 0) {
            return;
        }

        $this->applyIFaceStrategy($iface);

        $this->pageCache->config()->setCacheExpirationInSeconds($expires);

        $this->pageCache->init();
    }

    public function disable(): void
    {
        $this->enabled = false;

        $this->pageCache::destroy();
    }

    protected function applyIFaceStrategy(IFaceInterface $iface)
    {
        $this->strategy->setIFaceModel($iface->getModel());
        $this->pageCache->setStrategy($this->strategy);

        return $this;
    }
}
