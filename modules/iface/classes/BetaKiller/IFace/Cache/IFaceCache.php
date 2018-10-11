<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\IFace\IFaceInterface;
use PageCache\PageCache;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class IFaceCache
{
    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $appConfig;

    /**
     * @var PageCache
     */
    private $pageCache;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * IFaceCache constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Helper\AppEnvInterface    $appEnv
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(
        AppConfigInterface $appConfig,
        AppEnvInterface $appEnv,
        LoggerInterface $logger
    ) {
        $this->enabled   = !$appEnv->isCLI() && $appConfig->isPageCacheEnabled();
        $this->appConfig = $appConfig;

        $this->pageCache = new PageCache;
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
     * @param \BetaKiller\IFace\IFaceInterface         $iface
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \PageCache\PageCacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function process(IFaceInterface $iface, ServerRequestInterface $request): void
    {
        if (!$this->enabled) {
            return;
        }

        $expires = $iface->getExpiresSeconds();

        // Skip caching if content expired already (admin interfaces and non-cachable pages)
        if ($expires < 0) {
            return;
        }

        $strategy = new IFacePageCacheStrategy($request);

        $this->pageCache->setStrategy($strategy);

        $this->pageCache->config()
            ->setCacheExpirationInSeconds($expires)
            ->setCachePath($this->appConfig->getPageCachePath())
            ->setEnableLog(true)
            ->setSendHeaders(true)
            ->setForwardHeaders(true);

        $this->pageCache->init();
    }

    public function disable(): void
    {
        $this->enabled = false;

        $this->pageCache::destroy();
    }
}
