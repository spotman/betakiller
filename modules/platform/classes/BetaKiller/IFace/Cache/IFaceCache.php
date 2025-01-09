<?php

namespace BetaKiller\IFace\Cache;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\DateTimeHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Url\IFaceModelInterface;
use DateInterval;
use PageCache\PageCache;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class IFaceCache implements IFaceCacheInterface
{
    /**
     * @var PageCache
     */
    private PageCache $pageCache;

    /**
     * @var bool
     */
    private bool $enabled;

    /**
     * IFaceCache constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Env\AppEnvInterface       $appEnv
     * @param \BetaKiller\Helper\UrlElementHelper   $elementHelper
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(
        private AppConfigInterface $appConfig,
        private AppEnvInterface $appEnv,
        private UrlElementHelper $elementHelper,
        LoggerInterface $logger
    ) {
        $this->enabled = !$appEnv->isCli() && $appConfig->isPageCacheEnabled();

        $this->pageCache = new PageCache();
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

        $model = $this->elementHelper->getInstanceModel($iface);

        if (!$model instanceof IFaceModelInterface) {
            throw new \LogicException();
        }

        // Skip caching (admin interfaces and non-cachable pages)
        if (!$model->isCacheEnabled()) {
            return;
        }

        $strategy = new \BetaKiller\IFace\Cache\IFacePageCacheStrategy($request);

        $this->pageCache->setStrategy($strategy);

        $cachePath = $this->appEnv->getCachePath($this->appConfig->getPageCachePath());

        $expiresIn = $model->getExpiresInterval() ?? new DateInterval('PT1H'); // 1 hour caching by default

        $this->pageCache->config()
            ->setCacheExpirationInSeconds(DateTimeHelper::dateIntervalToSeconds($expiresIn))
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
