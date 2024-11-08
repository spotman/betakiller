<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

final readonly class UtmMedium extends AbstractUtmMarkerUrlParameter
{
    public static function getQueryKey(): string
    {
        return 'utm_medium';
    }
}
