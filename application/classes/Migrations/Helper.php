<?php

class Migrations_Helper extends Kohana_Migrations_Helper
{
    /**
     * @param $className
     *
     * @return Migration
     */
    protected static function create_migration_instance($className)
    {
        return \BetaKiller\DI\Container::getInstance()->get($className);
    }
}
