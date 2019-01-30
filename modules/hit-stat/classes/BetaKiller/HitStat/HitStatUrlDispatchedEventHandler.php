<?php
namespace BetaKiller\HitStat;

use BetaKiller\MessageBus\EventHandlerInterface;
use BetaKiller\Service\HitService;

class HitStatUrlDispatchedEventHandler implements EventHandlerInterface
{
    /**
     * @param \BetaKiller\Event\UrlDispatchedEvent $message
     */
    public function handleEvent($message): void
    {
        $params = $message->getUrlContainer();

        // Fetch UTM tags if exists so IFace would not warn about unused parameters
        $params->getQueryPart(HitService::UTM_SOURCE);
        $params->getQueryPart(HitService::UTM_MEDIUM);
        $params->getQueryPart(HitService::UTM_CAMPAIGN);
        $params->getQueryPart(HitService::UTM_CONTENT);
        $params->getQueryPart(HitService::UTM_TERM);
    }
}
