<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\IFace\IFaceInterface;
use PageCache\PageCache;
use PageCache\StrategyInterface;
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

    public function __construct(AppConfigInterface $config, PageCache $pageCache, LoggerInterface $logger)
    {
        $this->enabled = $config->isPageCacheEnabled();

        $this->pageCache = $pageCache;

        $this->pageCache->config()
            ->setCachePath($config->getPageCachePath())
            ->setEnableLog(true)
            ->setSendHeaders(true)
            ->setForwardHeaders(true);

        $this->pageCache->setLogger($logger);
//        $this->pageCache->setLogFilePath('/tmp/page-cache.log');
    }

    public function clearModelCache(/* IFaceRelatedModelInterface $model */): void
    {
        // deal with child ifaces (clear cache for whole branch)
//        $iface = $model->get_public_iface();

        throw new \HTTP_Exception_501('Not implemented yet');
    }

    public function clearCache(): void
    {
        $this->pageCache->clearAllCache();
    }

    public function process(IFaceInterface $iface): void
    {
        if (!$this->enabled) {
            return;
        }

        $expires = $iface->getExpiresSeconds();

        // Skip caching if content expired already
        if ($expires < 0) {
            return;
        }

        $this->applyIFaceStrategy($iface);

        $this->pageCache->config()->setCacheExpirationInSeconds($expires);

        $this->pageCache->init();
    }

    protected function applyIFaceStrategy(IFaceInterface $iface)
    {
        $strategy = $this->ifacePageCacheStrategyFactory($iface);
        $this->pageCache->setStrategy($strategy);

        return $this;
    }

    protected function ifacePageCacheStrategyFactory(IFaceInterface $iface): StrategyInterface
    {
        return new IFacePageCacheStrategy($iface);
    }
}
