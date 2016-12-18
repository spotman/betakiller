<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Assets_Model_ORM_Image extends Assets_Model_ORM implements Assets_Model_ImageInterface
{
    const SIZE_ORIGINAL = Assets_Provider_Image::SIZE_ORIGINAL;
    const SIZE_PREVIEW = Assets_Provider_Image::SIZE_PREVIEW;

    public function get_preview_url($size = NULL)
    {
        return $this->loaded()
            ? $this->get_provider()->get_preview_url($this, $size)
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

    /**
     * @return int
     */
    public function get_width()
    {
        return $this->get('width');
    }

    /**
     * @return int
     */
    public function get_height()
    {
        return $this->get('height');
    }

    /**
     * @param int $value
     * @return $this
     */
    public function set_width($value)
    {
        return $this->set('width', (int) $value);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function set_height($value)
    {
        return $this->set('height', (int) $value);
    }

    public function get_attributes_for_img_tag($size, array $attributes = [])
    {
        return $this->get_provider()->get_arguments_for_img_tag($this, $size, $attributes);
    }
}
