<?php
namespace BetaKiller\Config;


class AppConfig implements AppConfigInterface
{
    const APP_CONFIG_GROUP_NAME = 'app';
    const CONFIG_PATH_IS_TRAILING_SLASH_ENABLED = ['url', 'is_trailing_slash_enabled'];
    const CONFIG_PATH_CIRCULAR_LINK_HREF = ['url', 'circular_link_href'];
    const CONFIG_PATH_PAGE_CACHE_ENABLED = ['cache', 'page', 'enabled'];
    const CONFIG_PATH_PAGE_CACHE_PATH = ['cache', 'page', 'path'];

    /**
     * @var ConfigProviderInterface
     */
    private $_config;

    /**
     * AppConfig constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $_config
     */
    public function __construct(ConfigProviderInterface $_config)
    {
        $this->_config = $_config;
    }

    /**
     * Returns namespace for app-related classes (ifaces, widgets, factories, etc) or NULL if these classes located at root namespace
     *
     * @return string|null
     */
    public function getNamespace()
    {
        return $this->get(['namespace']);
    }

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function isTrailingSlashEnabled()
    {
        return (bool) $this->get(self::CONFIG_PATH_IS_TRAILING_SLASH_ENABLED);
    }

    /**
     * @return string
     */
    public function getCircularLinkHref()
    {
        return $this->get(self::CONFIG_PATH_CIRCULAR_LINK_HREF);
    }

    /**
     * @param string|array  $path
     * @param null          $default
     *
     * @return array|\BetaKiller\Config\ConfigGroupInterface|null|string
     */
    protected function get(array $path, $default = null)
    {
        return $this->_config->load(array_merge([self::APP_CONFIG_GROUP_NAME], $path)) ?: $default;
    }

    /**
     * @return bool
     */
    public function isPageCacheEnabled()
    {
        return $this->get(self::CONFIG_PATH_PAGE_CACHE_ENABLED, false);
    }

    /**
     * @return string
     */
    public function getPageCachePath()
    {
        return rtrim($this->get(self::CONFIG_PATH_PAGE_CACHE_PATH), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }
}
