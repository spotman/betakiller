<?php
namespace BetaKiller\Content;

use ORM;

interface HasWordpressPathInterface
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

    /**
     * @param string $wp_path
     * @return $this|null
     */
    public function find_by_wp_path($wp_path);

    /**
     * @param string $wp_path
     * @return $this|ORM
     */
    public function filter_wp_path($wp_path);
}
