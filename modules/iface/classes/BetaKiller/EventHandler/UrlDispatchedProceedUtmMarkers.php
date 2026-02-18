<?php

namespace BetaKiller\EventHandler;

use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Exception\DomainException;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Url\Parameter\UtmCampaign;
use BetaKiller\Url\Parameter\UtmContent;
use BetaKiller\Url\Parameter\UtmMedium;
use BetaKiller\Url\Parameter\UtmSource;
use BetaKiller\Url\Parameter\UtmTerm;

final readonly class UrlDispatchedProceedUtmMarkers
{
    public static function getUtmQueryKeys(): array
    {
        return [
            UtmSource::getQueryKey(),
            UtmMedium::getQueryKey(),
            UtmCampaign::getQueryKey(),
            UtmContent::getQueryKey(),
            UtmTerm::getQueryKey(),
        ];
    }

    public function __invoke(UrlDispatchedEvent $message): void
    {
        $params = $message->getUrlContainer();

        // Fetch UTM tags if exists so IFace would not warn about unused parameters
        foreach (self::getUtmQueryKeys() as $queryKey) {
            $value = $params->getQueryPart($queryKey);

            if ($value) {
                $params->setParameter($this->createUtmParameter($queryKey, $value));
            }
        }
    }

    private function createUtmParameter(string $queryKey, string $value): UrlParameterInterface
    {
        switch ($queryKey) {
            case UtmSource::getQueryKey():
                return UtmSource::fromUriValue($value);

            case UtmMedium::getQueryKey():
                return UtmMedium::fromUriValue($value);

            case UtmCampaign::getQueryKey():
                return UtmCampaign::fromUriValue($value);

            case UtmContent::getQueryKey():
                return UtmContent::fromUriValue($value);

            case UtmTerm::getQueryKey():
                return UtmTerm::fromUriValue($value);

            default:
                throw new DomainException('Unknown UTM query key ":key"', [
                    ':key' => $queryKey,
                ]);
        }
    }
}
