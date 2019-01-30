<?php
namespace BetaKiller\HitStat;

use BetaKiller\MessageBus\EventHandlerInterface;
use BetaKiller\Model\HitMarkerInterface;

class HitStatUrlDispatchedEventHandler implements EventHandlerInterface
{
    /**
     * @param \BetaKiller\Event\UrlDispatchedEvent $message
     */
    public function handleEvent($message): void
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
