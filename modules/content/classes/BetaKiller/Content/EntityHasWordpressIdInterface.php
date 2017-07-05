<?php
namespace BetaKiller\Content;

interface EntityHasWordpressIdInterface
{
    /**
     * @param int $value
     *
     * @return $this|\ORM
     */
    public function set_wp_id($value);

    /**
     * @return int|null
     */
    public function get_wp_id();
}
