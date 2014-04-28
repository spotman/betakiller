<?php defined('SYSPATH') or die('No direct script access.');

class Task_Assets_Static_Clear extends Minion_Task {

    protected function _execute(array $params)
    {
        try
        {
            StaticFile::instance()->cache_reset();
        }
        catch ( Kohana_Exception $e )
        {
            die('Error: '.$e->getMessage());
        }
    }

}