<?php

trait Model_ORM_HasWordpressPathTrait
{
    /**
     * @param string $value
     * @return $this|ORM
     */
    public function set_wp_path($value)
    {
        return $this->set('wp_path', $value);
    }

    /**
     * @return string|null
     */
    public function get_wp_path()
    {
        return $this->get('wp_path');
    }

    /**
     * @param string $wp_path
     * @return $this|null
     */
    public function find_by_wp_path($wp_path)
    {
        $model = $this->filter_wp_path($wp_path)->find();

        return $model->loaded() ? $model : NULL;
    }

    /**
     * @param string $wp_path
     * @return $this|ORM
     */
    public function filter_wp_path($wp_path)
    {
        return $this->where('wp_path', '=', $wp_path);
    }
}
