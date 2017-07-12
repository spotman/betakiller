<?php
namespace BetaKiller\Content\CustomTag;

abstract class AbstractCustomTag implements CustomTagInterface
{
    public static function getCodename(): string
    {
        $className = static::class;
        $pos       = strrpos($className, '\\');
        $baseName  = substr($className, $pos + 1);

        return str_replace(self::CLASS_SUFFIX, '', $baseName);
    }
}
