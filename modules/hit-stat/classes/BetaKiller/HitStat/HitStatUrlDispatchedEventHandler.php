<?php
namespace BetaKiller\HitStat;

use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Model\HitMarkerInterface;

class HitStatUrlDispatchedEventHandler
{
    /**
     * @param \BetaKiller\Event\UrlDispatchedEvent $message
     */
    public function __invoke(UrlDispatchedEvent $message): void
    {
        $params = $message->getUrlContainer();

        // Fetch UTM tags if exists so IFace would not warn about unused parameters
        foreach (HitMarkerInterface::UTM_QUERY_KEYS as $key) {
            $params->getQueryPart($key);
        }
    }
}
