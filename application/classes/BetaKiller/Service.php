<?php
namespace BetaKiller;

use BetaKiller\DI\Container;

abstract class Service
{
    public static function instance()
    {
        return Container::getInstance()->get(static::class);
    }
}
