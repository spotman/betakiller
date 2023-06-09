<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

final class UtmMediumUrlParameter extends AbstractUtmMarkerUrlParameter
{
    public const QUERY_KEY = 'utm_medium';

    public static function getQueryKey(): string
    {
        return self::QUERY_KEY;
    }
}
