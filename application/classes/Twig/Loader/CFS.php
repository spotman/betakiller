<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Twig loader for Kohana's cascading filesystem
 */
class Twig_Loader_CFS extends Kohana_Twig_Loader_CFS {

    public function getSource($name)
    {
        $str = parent::getSource($name);

        // Replace static url`s keys
        $str = StaticFile::instance()->replace_keys($str);

        return $str;
    }

}
