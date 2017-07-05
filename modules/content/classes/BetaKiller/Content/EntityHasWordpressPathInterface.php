<?php
namespace BetaKiller\Content;

use BetaKiller\Model\AbstractEntityInterface;
use ORM;

interface EntityHasWordpressPathInterface extends AbstractEntityInterface
{
    /**
     * @param string $value
     * @return $this|ORM
     */
    public function set_wp_path($value);

    /**
     * @return string|null
     */
    public function get_wp_path();
}
