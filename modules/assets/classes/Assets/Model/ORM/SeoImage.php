<?php


abstract class Assets_Model_ORM_SeoImage extends Assets_Model_ORM_Image
{
    /**
     * @param string $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_alt($value)
    {
        return $this->set('alt', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_alt()
    {
        return $this->get('alt');
    }

    /**
     * @param string $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_title($value)
    {
        return $this->set('title', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_title()
    {
        return $this->get('title');
    }

    public function get_attributes_for_img_tag($size, array $attributes = [])
    {
        $attributes = array_merge([
            'alt'       =>  $this->get_alt(),
            'title'     =>  $this->get_title(),
        ], $attributes);

        return parent::get_attributes_for_img_tag($size, $attributes);
    }
}
