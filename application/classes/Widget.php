<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Widget extends Kohana_Widget {

    /**
     * Returns Twig view instance
     *
     * @param null $file
     * @param array $data
     * @return Twig
     */
    protected function view_factory($file = NULL, array $data = NULL)
    {
        return Twig::factory($file, $data);
    }

}