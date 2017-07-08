<?php

namespace BetaKiller\Model;

use BetaKiller\Helper\SeoMetaInterface;
use Kohana_Exception;

trait OrmBasedSeoMetaTrait
{
    /**
     * @param string $value
     *
     * @return SeoMetaInterface
     * @throws Kohana_Exception
     */
    public function setTitle(string $value): SeoMetaInterface
    {
        return $this->set('title', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getTitle(): ?string
    {
        return $this->get('title');
    }

    /**
     * @param string $value
     *
     * @return SeoMetaInterface
     * @throws Kohana_Exception
     */
    public function setDescription(string $value): SeoMetaInterface
    {
        return $this->set('description', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getDescription(): ?string
    {
        return $this->get('description');
    }
}
