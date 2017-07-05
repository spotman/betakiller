<?php

trait Model_ORM_EntityHasWordpressIdTrait
{
    /**
     * @param int $value
     * @return $this|ORM
     */
    public function set_wp_id($value)
    {
        return $this->set('wp_id', (int) $value);
    }

    /**
     * @return int|null
     */
    public function get_wp_id()
    {
        return $this->get('wp_id');
    }
}
