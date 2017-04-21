<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\Config\AppConfigInterface;
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

    public function __construct(AppConfigInterface $config, LoggerInterface $logger)
    {
        $this->enabled = $config->isPageCacheEnabled();

        $this->pageCache = new PageCache;

        $this->pageCache->setPath($config->getPageCachePath());
        $this->pageCache->enableLog();
        $this->pageCache->setLogger($logger);
//        $this->pageCache->setLogFilePath('/tmp/page-cache.log');
    }

    public function clearModelCache(/* IFaceRelatedModelInterface $model */)
    {
        // deal with child ifaces (clear cache for whole branch)
//        $iface = $model->get_public_iface();

        throw new \HTTP_Exception_501('Not implemented yet');
    }

    public function clearCache()
    {
        $this->pageCache->clearCache();
    }

    public function process(IFaceInterface $iface)
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

        $this->pageCache->setExpiration($expires);
        $this->pageCache->enableHeaders(true);
        $this->pageCache->forwardHeaders(true);
        $this->pageCache->init();
    }

    protected function applyIFaceStrategy(IFaceInterface $iface)
    {
        $strategy = $this->ifacePageCacheStrategyFactory($iface);
        $this->pageCache->setStrategy($strategy);

        return $this;
    }

    protected function ifacePageCacheStrategyFactory(IFaceInterface $iface)
    {
        return new IFacePageCacheStrategy($iface);
    }
}
