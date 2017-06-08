<?php

use BetaKiller\Helper\SeoMetaInterface;

trait Model_ORM_SeoContentTrait
{
    /**
     * @param string $value
     * @return SeoMetaInterface
     * @throws Kohana_Exception
     */
    public function setTitle(string $value)
    {
        return $this->set('title', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getTitle(): ?string
    {
        return (string)$this->get('title');
    }

    /**
     * @param string $value
     * @return SeoMetaInterface
     * @throws Kohana_Exception
     */
    public function setDescription(string $value)
    {
        return $this->set('description', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getDescription(): ?string
    {
        return (string)$this->get('description');
    }
}
