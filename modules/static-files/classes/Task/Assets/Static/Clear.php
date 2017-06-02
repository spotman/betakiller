<?php defined('SYSPATH') or die('No direct script access.');

class Task_Assets_Static_Clear extends Minion_Task {

    protected function _execute(array $params)
    {
        try
        {
            $paths = StaticFile::instance()->get_cache_folders();

            foreach ($paths as $path)
            {
                Minion_CLI::write('Erasing '.$path);
                File::rmdir($path);
            }

            Minion_CLI::write('Static assets cache was successfully cleared');
        }
        catch (Throwable $e)
        {
            Minion_CLI::write('Error: '.$e->getMessage());
        }
    }

}
