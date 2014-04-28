<?php defined('SYSPATH') or die('No direct script access.');

class Task_Assets_Static_Clear extends Minion_Task {

    protected function _execute(array $params)
    {
        try
        {
            StaticFile::instance()->cache_reset();
            Minion_CLI::write('Static assets cache was successfully cleared');
        }
        catch ( Kohana_Exception $e )
        {
            Minion_CLI::write('Error: '.$e->getMessage());
        }
    }

}