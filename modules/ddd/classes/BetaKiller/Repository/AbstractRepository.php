<?php
namespace BetaKiller\Repository;


abstract class AbstractRepository implements RepositoryInterface
{
    public static function getCodename(): string
    {
        $className = static::class;
        $pos = strrpos($className, '\\');
        $baseName = substr($className, $pos + 1);
        return str_replace(self::CLASS_SUFFIX, '', $baseName);
    }
}
