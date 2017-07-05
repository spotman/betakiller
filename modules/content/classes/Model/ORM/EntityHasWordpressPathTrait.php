<?php

trait Model_ORM_EntityHasWordpressPathTrait
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
}
