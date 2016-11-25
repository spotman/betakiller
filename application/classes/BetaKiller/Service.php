<?php
namespace BetaKiller;

use BetaKiller\DI\Container;
use BetaKiller\Helper\Base;

abstract class Service
{
    use Base;

    public static function instance()
    {
        return Container::instance()->get(static::class);
    }
}
