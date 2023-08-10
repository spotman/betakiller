<?php
declare(strict_types=1);

namespace BetaKiller\Url;

abstract class AbstractMenuCounter implements MenuCounterInterface
{
    public static function codename(): string
    {
        $codename = explode('\\', static::class);
        array_splice($codename, 0, -1 * \count($codename) + 2);
        $codename = implode('\\', $codename);

        $suffix   = static::SUFFIX;
        $codename = preg_replace('/(.+?)'.$suffix.'$/', '$1', $codename);

        return $codename;
    }
}
