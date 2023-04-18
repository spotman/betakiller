<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Env\AppEnvInterface;
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
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * IFaceCache constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Env\AppEnvInterface       $appEnv
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(
        AppConfigInterface $appConfig,
        AppEnvInterface $appEnv,
        LoggerInterface $logger
    ) {
        $this->enabled   = !$appEnv->isCli() && $appConfig->isPageCacheEnabled();
        $this->appEnv    = $appEnv;
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

        // Skip caching if request method is not GET nor HEAD
        if (!in_array(mb_strtoupper($request->getMethod()), ['GET', 'HEAD'], true)) {
            return;
        }

        // Skip caching (admin interfaces and non-cachable pages)
        if (!$iface->isHttpCachingEnabled()) {
            return;
        }

        $strategy = new IFacePageCacheStrategy($request);

        $this->pageCache->setStrategy($strategy);

        $cachePath = $this->appEnv->getCachePath($this->appConfig->getPageCachePath());

        $this->pageCache->config()
            ->setCacheExpirationInSeconds($iface->getExpiresSeconds())
            ->setCachePath($cachePath)
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
