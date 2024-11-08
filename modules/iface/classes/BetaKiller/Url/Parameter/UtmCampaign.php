<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

final readonly class UtmCampaign extends AbstractUtmMarkerUrlParameter
{
    public static function getQueryKey(): string
    {
        return 'utm_campaign';
    }
}
