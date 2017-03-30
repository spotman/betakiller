<?php
namespace BetaKiller\Api;

use BetaKiller\DI\Container;

class API extends \Spotman\Api\API
{
    /**
     * Method for backward compatibility and transparent refactoring
     *
     * @deprecated Use DI in client`s class constructor instead
     * @return static
     */
    public static function getInstance()
    {
        return Container::instance()->get(static::class);
    }
}
