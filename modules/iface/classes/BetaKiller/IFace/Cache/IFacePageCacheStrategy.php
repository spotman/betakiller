<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\Helper\UrlHelper;
use BetaKiller\Url\IFaceModelInterface;
use PageCache\SessionHandler;
use PageCache\StrategyInterface;

class IFacePageCacheStrategy implements StrategyInterface
{
    /**
     * @var \BetaKiller\Url\IFaceModelInterface
     */
    protected $ifaceModel;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $helper;

    /**
     * IFacePageCacheStrategy constructor.
     *
     * @param \BetaKiller\Helper\UrlHelper $helper
     */
    public function __construct(UrlHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     */
    public function setIFaceModel(IFaceModelInterface $model): void
    {
        $this->ifaceModel = $model;
    }

    /**
     * Sets cache file name
     *
     * @return string Cache file name
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function strategy(): string
    {
        // When session support is enabled add that to file name
        $sessionFingerprint = SessionHandler::process();

        $uri = $this->helper->makeUrl($this->ifaceModel, null, false);

        return md5($sessionFingerprint.$uri);
    }
}
