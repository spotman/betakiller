<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Assets_Model_ORM_Image extends Assets_Model_ORM implements Assets_Model_ImageInterface
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

    public function get_arguments_for_img_tag($url, array $attributes = [])
    {
        $attributes = array_merge([
            'src'       =>  $url,
            'srcset'    =>  $this->get_srcset(),
            'width'     =>  $this->get_width(),
            'height'    =>  $this->get_height(),
        ], $attributes);

        return $attributes;
    }

    public function get_srcset()
    {
        $sizes = $this->get_provider()->get_allowed_preview_sizes();
        $srcset = [];

        if ($sizes)
        {
            foreach ($sizes as $size)
            {
                $width = intval($size);
                $url = $this->get_preview_url($size);
                $srcset[] = $this->make_srcset_width_option($url, $width);
            }

            // Add srcset for original image
            $url = $this->get_original_url();
            $srcset[] = $this->make_srcset_width_option($url, $this->get_width());
        }

        return implode(', ', $srcset);
    }

    protected function make_srcset_width_option($url, $width)
    {
        return $url.' '.$width.'w';
    }
}
