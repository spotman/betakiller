<?php
namespace BetaKiller\Model;

use ORM;

interface EntityHasWordpressPathInterface extends AbstractEntityInterface
{
    /**
     * @param string $value
     * @return $this|ORM
     */
    public function setWpPath(string $value);

    /**
     * @return string|null
     */
    public function getWpPath(): ?string;
}
