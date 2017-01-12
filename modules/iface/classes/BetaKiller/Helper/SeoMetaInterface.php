<?php
namespace BetaKiller\Helper;

interface SeoMetaInterface
{
    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     * @return $this
     */
    public function set_title($value);

    /**
     * Returns title for using in <title> tag
     *
     * @return string
     */
    public function get_title();

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     * @return $this
     */
    public function set_description($value);

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function get_description();
}
