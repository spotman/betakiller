<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Url\UrlContainerInterface;
use PageCache\SessionHandler;
use PageCache\StrategyInterface;

class IFacePageCacheStrategy implements StrategyInterface
{
    /**
     * @var IFaceInterface
     */
    protected $iface;

    /**
     * @var \BetaKiller\Url\UrlContainerInterface
     */
    protected $params;

    /**
     * IFacePageCacheStrategy constructor.
     *
     * @param IFaceInterface                             $iface
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     */
    public function __construct(IFaceInterface $iface, UrlContainerInterface $params = null)
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
