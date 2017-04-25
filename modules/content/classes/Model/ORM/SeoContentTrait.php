<?php

use BetaKiller\Helper\SeoMetaInterface;

trait Model_ORM_SeoContentTrait
{
    /**
     * @param string $value
     * @return SeoMetaInterface
     * @throws Kohana_Exception
     */
    public function setTitle($value)
    {
        return $this->set('title', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getTitle()
    {
        return $this->get('title');
    }

    /**
     * @param string $value
     * @return SeoMetaInterface
     * @throws Kohana_Exception
     */
    public function setDescription($value)
    {
        return $this->set('description', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getDescription()
    {
        return $this->get('description');
    }
}
