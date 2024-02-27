<?php

use BetaKiller\Env\AppEnvInterface;

/**
 * Interface that all minion tasks must implement
 */
abstract class Minion_Task extends Kohana_Minion_Task
{
    public function __construct()
    {
        // Migrations are executed by deployer with --stage option
        $this->_options[AppEnvInterface::CLI_OPTION_STAGE] = AppEnvInterface::MODE_DEVELOPMENT;

        // Migrations can be executed with --debug option
        $this->_options[AppEnvInterface::CLI_OPTION_DEBUG] = false;

        parent::__construct();
    }
}
