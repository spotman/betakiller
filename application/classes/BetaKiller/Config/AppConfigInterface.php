<?php
namespace BetaKiller\Config;


interface AppConfigInterface
{
    /**
     * Returns namespace for app-related classes (ifaces, widgets, factories, etc) or NULL if these classes located at root namespace
     *
     * @return string|null
     */
    public function get_namespace();

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function is_trailing_slash_enabled();

    /**
     * @return string
     */
    public function get_circular_link_href();

    /**
     * @return bool
     */
    public function is_page_cache_enabled();

    /**
     * @return string
     */
    public function get_page_cache_path();
}
