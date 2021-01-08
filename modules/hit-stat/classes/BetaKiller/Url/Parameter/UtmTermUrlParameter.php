<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

final class UtmTermUrlParameter extends AbstractUtmMarkerUrlParameter
{
    public const QUERY_KEY = 'utm_term';

    public static function getQueryKey(): string
    {
        return self::QUERY_KEY;
    }
}
