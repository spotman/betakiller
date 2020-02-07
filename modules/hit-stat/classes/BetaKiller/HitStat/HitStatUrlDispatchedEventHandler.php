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
        $params->getQueryPart(HitMarkerInterface::UTM_QUERY_SOURCE);
        $params->getQueryPart(HitMarkerInterface::UTM_QUERY_MEDIUM);
        $params->getQueryPart(HitMarkerInterface::UTM_QUERY_CAMPAIGN);
        $params->getQueryPart(HitMarkerInterface::UTM_QUERY_CONTENT);
        $params->getQueryPart(HitMarkerInterface::UTM_QUERY_TERM);
    }
}
