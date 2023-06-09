<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

final class UtmContentUrlParameter extends AbstractUtmMarkerUrlParameter
{
    public const QUERY_KEY = 'utm_content';

    public static function getQueryKey(): string
    {
        return self::QUERY_KEY;
    }
}
