<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Assets_Provider_Image
 * Abstract class for all image assets
 */
abstract class Assets_Provider_Image extends Assets_Provider {

    /**
     * @param Assets_Model $model
     * @return string
     * @throws HTTP_Exception_501
     */
    public function get_preview_url(Assets_Model $model)
    {
        return $this->_get_item_url('preview', $model);
    }

    /**
     * @return int
     */
    abstract public function get_preview_max_height();

    /**
     * @return int
     */
    abstract public function get_preview_max_width();

    /**
     * @return int
     */
    abstract public function get_preview_quality();
}