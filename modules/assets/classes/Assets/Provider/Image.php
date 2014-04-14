<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Assets_Provider_Image
 * Abstract class for all image assets
 */
abstract class Assets_Provider_Image extends Assets_Provider {

    /**
     * @param Assets_File_Model $model
     * @return string
     * @throws HTTP_Exception_501
     */
    public function get_preview_url(Assets_File_Model $model)
    {
        // TODO Implement
        return StaticFile::instance()->getLink("temp/devjaty-val-ajvazovsky.jpg");

    }

    abstract protected function get_preview_max_height();
    abstract protected function get_preview_max_width();
}