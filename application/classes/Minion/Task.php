<?php

/**
 * Interface that all minion tasks must implement
 */
abstract class Minion_Task extends Kohana_Minion_Task
{
    public function __construct()
    {
        // Migrations are executed by deployer with --stage option
        $this->_options['stage'] = 'development';

        // Migrations can be executed with --debug option
        $this->_options['debug'] = false;

        parent::__construct();
    }
}
