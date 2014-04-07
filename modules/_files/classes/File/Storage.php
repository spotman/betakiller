<?php defined('SYSPATH') OR die('No direct script access.');


abstract class File_Storage {

    /**
     * @param string $codename
     * @return static
     * @throws File_Storage_Exception
     */
    public static function factory($codename)
    {
        $class_name = 'File_Provider_'.$codename;

        if ( ! class_exists($class_name) )
            throw new File_Storage_Exception('Unknown storage :class', array(':class' => $class_name));

        $instance = new $class_name($codename);

        // TODO

        return $instance;
    }

}