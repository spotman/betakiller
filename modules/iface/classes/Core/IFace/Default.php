<?php defined('SYSPATH') OR die('No direct script access.');

class Core_IFace_Default extends IFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        // Empty by default
        return array();
    }
}
