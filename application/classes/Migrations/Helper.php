<?php

class Migrations_Helper extends Kohana_Migrations_Helper
{
    /**
     * @param $class_name
     *
     * @return Migration
     */
    protected static function create_migration_instance($class_name)
    {
        return \BetaKiller\DI\Container::getInstance()->get($class_name);
    }
}
