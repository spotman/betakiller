<?php defined('SYSPATH') OR die('No direct script access.');


class Task_Assets_Merge extends Minion_Task {

    /**
     * The list of options this task accepts and their default values.
     *
     *     protected $_options = array(
     *         'limit' => 4,
     *         'table' => NULL,
     *     );
     *
     * @var array
     */
    protected $_options = array(
        'target'    =>  ''
    );

    protected function _execute(array $params)
    {
        $static_files_list = Kohana::list_files('static-files');

        $target_directory = MultiSite::instance()->site_path().DIRECTORY_SEPARATOR.'builds'.DIRECTORY_SEPARATOR.'merge';

//        Minion_CLI::write($target_directory);

        foreach( $static_files_list as $file )
        {
            $this->process_file($file, $target_directory);
        }

//        var_dump($static_files_list);
    }

    protected function process_file($file, $target_base)
    {
        if ( is_array($file) )
        {
            foreach ( $file as $item )
            {
                $this->process_file($item, $target_base);
            }
        }
        else
        {
            $file_array = explode('static-files'.DIRECTORY_SEPARATOR, $file);

            $target = $target_base.DIRECTORY_SEPARATOR.$file_array[1];

            $target_base_dir = dirname($target);

            if ( ! file_exists($target_base_dir) )
            {
                mkdir($target_base_dir, 0775, TRUE);
            }

            Minion_CLI::color($file, 'green');
            Minion_CLI::color($target, 'blue');

//            copy($file, $target);
        }

    }

}
