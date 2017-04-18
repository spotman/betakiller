<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\IFace\IFaceInterface;
use PageCache\SessionHandler;
use PageCache\StrategyInterface;

class IFacePageCacheStrategy implements StrategyInterface
{
    /**
     * @var IFaceInterface
     */
    protected $iface;

    /**
     * @var \BetaKiller\IFace\Url\UrlParameters
     */
    protected $params;

    /**
     * IFacePageCacheStrategy constructor.
     *
     * @param IFaceInterface                           $iface
     * @param \BetaKiller\IFace\Url\UrlParameters|null $params
     */
    public function __construct(IFaceInterface $iface, \BetaKiller\IFace\Url\UrlParameters $params = null)
    {
        $this->iface  = $iface;
        $this->params = $params;
    }

    /**
     * Sets cache file name
     *
     * @return string Cache file name
     */
    public function strategy()
    {
        //when session support is enabled add that to file name
        $session_str = SessionHandler::process();

        $uri = $this->iface->url($this->params, false, false);

        return md5($session_str.$uri);
    }
}
