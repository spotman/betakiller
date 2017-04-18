<?php
namespace BetaKiller\Config;


interface AppConfigInterface
{
    /**
     * Returns namespace for app-related classes (ifaces, widgets, factories, etc) or NULL if these classes located at root namespace
     *
     * @return string|null
     */
    public function getNamespace();

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function isTrailingSlashEnabled();

    /**
     * @return string
     */
    public function getCircularLinkHref();

    /**
     * @return bool
     */
    public function isPageCacheEnabled();

    /**
     * @return string
     */
    public function getPageCachePath();
}
