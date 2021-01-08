<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

final class UtmSourceUrlParameter extends AbstractUtmMarkerUrlParameter
{
    public const QUERY_KEY = 'utm_source';

    public static function getQueryKey(): string
    {
        return self::QUERY_KEY;
    }
}
