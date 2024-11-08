<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

final readonly class UtmContent extends AbstractUtmMarkerUrlParameter
{
    public static function getQueryKey(): string
    {
        return 'utm_content';
    }
}
