<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\IFaceModelInterface;
use PageCache\SessionHandler;
use PageCache\StrategyInterface;

class IFacePageCacheStrategy implements StrategyInterface
{
    /**
     * @var \BetaKiller\IFace\IFaceModelInterface
     */
    protected $ifaceModel;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $helper;

    /**
     * IFacePageCacheStrategy constructor.
     *
     * @param \BetaKiller\Helper\IFaceHelper $helper
     */
    public function __construct(IFaceHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     */
    public function setIFaceModel(IFaceModelInterface $model): void
    {
        $this->ifaceModel = $model;
    }

    /**
     * Sets cache file name
     *
     * @return string Cache file name
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function strategy(): string
    {
        // When session support is enabled add that to file name
        $sessionFingerprint = SessionHandler::process();

        $uri = $this->helper->makeUrl($this->ifaceModel, null, false);

        return md5($sessionFingerprint.$uri);
    }
}
