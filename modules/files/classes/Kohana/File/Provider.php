<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_File_Provider {

    public static function factory($name)
    {
        $class_name = 'File_Provider_'.$name;
    }


}