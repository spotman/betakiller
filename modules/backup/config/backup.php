<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    /**
     * YandexDisk

    'service'   =>  'YandexDisk',
    'login'     =>  'test@yandex.ru',
    'password'  =>  'test',
    'type'      =>  YandexBackup::ZIP,

     *
     */

    'database'  =>  'default',
    'folder'    =>  realpath(DOCROOT.'..'.DIRECTORY_SEPARATOR),

    'useTimestampedPrefix' => false,
    'prefix'    =>  date('Y-M-l'),

);
