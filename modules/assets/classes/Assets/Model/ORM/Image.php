<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Assets_Model_ORM_Image extends Assets_Model_ORM
{
    public function get_preview_url($size = NULL)
    {
        return $this->loaded()
            ? $this->get_provider()->get_preview_url($this, $size)
            : NULL;
    }

    public function get_crop_url($size = NULL)
    {
        return $this->loaded()
            ? $this->get_provider()->get_crop_url($this, $size)
            : NULL;
    }

    public function get_upload_max_width()
    {
        return $this->get_provider()->get_upload_max_width();
    }

    public function get_upload_max_height()
    {
        return $this->get_provider()->get_upload_max_height();
    }
}
