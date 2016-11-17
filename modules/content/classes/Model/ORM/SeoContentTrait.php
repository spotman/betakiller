<?php

trait Model_ORM_SeoContentTrait
{
    /**
     * @param string $value
     * @return $this
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

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_description($value)
    {
        return $this->set('description', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_description()
    {
        return $this->get('description');
    }
}
