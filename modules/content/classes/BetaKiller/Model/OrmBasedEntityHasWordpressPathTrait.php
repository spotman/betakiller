<?php

namespace BetaKiller\Model;

use ORM;

/**
 * Trait OrmBasedEntityHasWordpressPathTrait
 *
 * @package BetaKiller\Content
 */
trait OrmBasedEntityHasWordpressPathTrait
{
    /**
     * @param string $value
     *
     * @return $this|ORM
     */
    public function setWpPath(string $value)
    {
        return $this->set('wp_path', $value);
    }

    /**
     * @return string|null
     */
    public function getWpPath(): ?string
    {
        return (string)$this->get('wp_path');
    }
}
