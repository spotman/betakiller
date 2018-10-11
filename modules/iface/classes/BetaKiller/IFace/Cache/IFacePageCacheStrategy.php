<?php
namespace BetaKiller\IFace\Cache;

use BetaKiller\Helper\ServerRequestHelper;
use PageCache\StrategyInterface;
use Psr\Http\Message\ServerRequestInterface;

final class IFacePageCacheStrategy implements StrategyInterface
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * IFacePageCacheStrategy constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Sets cache file name
     *
     * @return string Cache file name
     */
    public function strategy(): string
    {
        $session = ServerRequestHelper::getSession($this->request);

        // Add session fingerprint
        $sessionFingerprint = \json_encode($session);

        $url = ServerRequestHelper::getUrl($this->request);

        return md5($sessionFingerprint.$url);
    }
}
