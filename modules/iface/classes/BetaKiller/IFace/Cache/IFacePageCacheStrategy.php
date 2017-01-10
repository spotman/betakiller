<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\IFace\IFace;
use PageCache\StrategyInterface;
use PageCache\SessionHandler;

class IFacePageCacheStrategy implements StrategyInterface
{
    /**
     * @var IFace
     */
    protected $iface;

    /**
     * @var \URL_Parameters
     */
    protected $params;

    /**
     * IFacePageCacheStrategy constructor.
     *
     * @param IFace                 $iface
     * @param \URL_Parameters|null  $params
     */
    public function __construct(IFace $iface, \URL_Parameters $params = null)
    {
        $this->iface = $iface;
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

        $uri = $this->iface->url($this->params, FALSE, FALSE);

        return md5($session_str.$uri);
    }
}
