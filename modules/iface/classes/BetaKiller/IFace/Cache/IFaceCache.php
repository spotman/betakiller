<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\IFace\IFace;
use BetaKiller\IFace\IFaceRelatedModelInterface;
use PageCache\PageCache;

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

    public function __construct(AppConfigInterface $config)
    {
        $this->enabled = $config->is_cache_enabled();

        $this->pageCache = new PageCache;

        $this->pageCache->setPath($config->get_page_cache_path());
        // $this->pageCache->enableLog();
        // $this->pageCache->setLogFilePath("/tmp/page-cache.log");
    }

    public function clearModelCache(IFaceRelatedModelInterface $model)
    {
        // deal with child ifaces (clear cache for whole branch)
//        $iface = $model->get_public_iface();

        throw new \HTTP_Exception_501('Not implemented yet');
    }

    public function clearCache()
    {
        $this->pageCache->clearCache();
    }

    public function process(IFace $iface)
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
        $this->pageCache->init();
    }

    protected function applyIFaceStrategy(IFace $iface)
    {
        $strategy = $this->ifacePageCacheStrategyFactory($iface);
        $this->pageCache->setStrategy($strategy);

        return $this;
    }

    protected function ifacePageCacheStrategyFactory(IFace $iface)
    {
        return new IFacePageCacheStrategy($iface);
    }
}
