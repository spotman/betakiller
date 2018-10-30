<?php
namespace BetaKiller\Url;

abstract class AbstractUrlElement
{
    /**
     * @return string
     */
    final public static function codename(): string
    {
        $codename = explode('\\', static::class);
        array_splice($codename, 0, -1 * \count($codename) + 2);
        $codename = implode('_', $codename);

        $suffix   = static::getSuffix();
        $codename = preg_replace('/(.+?)'.$suffix.'$/', '$1', $codename);

        return $codename;
    }

    /**
     * @return string
     */
    abstract public static function getSuffix(): string;
}
