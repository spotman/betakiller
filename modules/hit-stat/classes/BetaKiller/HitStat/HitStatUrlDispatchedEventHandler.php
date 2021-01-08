<?php
namespace BetaKiller\HitStat;

use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Exception\DomainException;
use BetaKiller\Service\HitService;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Url\Parameter\UtmCampaignUrlParameter;
use BetaKiller\Url\Parameter\UtmContentUrlParameter;
use BetaKiller\Url\Parameter\UtmMediumUrlParameter;
use BetaKiller\Url\Parameter\UtmSourceUrlParameter;
use BetaKiller\Url\Parameter\UtmTermUrlParameter;

class HitStatUrlDispatchedEventHandler
{
    /**
     * @param \BetaKiller\Event\UrlDispatchedEvent $message
     */
    public function __invoke(UrlDispatchedEvent $message): void
    {
        $params = $message->getUrlContainer();

        // Fetch UTM tags if exists so IFace would not warn about unused parameters
        foreach (HitService::UTM_QUERY_KEYS as $queryKey) {
            $value = $params->getQueryPart($queryKey);

            if ($value) {
                $params->setParameter($this->createUtmParameter($queryKey, $value));
            }
        }
    }

    private function createUtmParameter(string $queryKey, string $value): UrlParameterInterface
    {
        switch ($queryKey) {
            case UtmSourceUrlParameter::QUERY_KEY:
                return new UtmSourceUrlParameter($value);

            case UtmMediumUrlParameter::QUERY_KEY:
                return new UtmMediumUrlParameter($value);

            case UtmCampaignUrlParameter::QUERY_KEY:
                return new UtmCampaignUrlParameter($value);

            case UtmContentUrlParameter::QUERY_KEY:
                return new UtmContentUrlParameter($value);

            case UtmTermUrlParameter::QUERY_KEY:
                return new UtmTermUrlParameter($value);

            default:
                throw new DomainException('Unknown UTM query key ":key"', [
                    ':key' => $queryKey,
                ]);
        }
    }
}
