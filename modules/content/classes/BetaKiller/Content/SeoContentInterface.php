<?php
namespace BetaKiller\Content;

interface SeoContentInterface
{
    /**
     * @param string $value
     * @return $this
     */
    public function set_title($value);

    /**
     * @return string
     */
    public function get_title();

    /**
     * @param string $value
     * @return $this
     */
    public function set_description($value);

    /**
     * @return string
     */
    public function get_description();
}
