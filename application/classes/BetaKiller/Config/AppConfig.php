<?php
namespace BetaKiller\Config;


class AppConfig implements AppConfigInterface
{
    const APP_CONFIG_GROUP_NAME = 'app';
    const CONFIG_PATH_IS_TRAILING_SLASH_ENABLED = ['url', 'is_trailing_slash_enabled'];
    const CONFIG_PATH_CIRCULAR_LINK_HREF = ['url', 'circular_link_href'];

    /**
     * @var ConfigInterface
     */
    private $_config;

    /**
     * AppConfig constructor.
     *
     * @param \BetaKiller\Config\ConfigInterface $_config
     */
    public function __construct(ConfigInterface $_config)
    {
        $this->_config = $_config;
    }

    /**
     * Returns namespace for app-related classes (ifaces, widgets, factories, etc) or NULL if these classes located at root namespace
     *
     * @return string|null
     */
    public function get_namespace()
    {
        return $this->get(['namespace']);
    }

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function is_trailing_slash_enabled()
    {
        return (bool) $this->get(self::CONFIG_PATH_IS_TRAILING_SLASH_ENABLED);
    }

    /**
     * @return string
     */
    public function get_circular_link_href()
    {
        return $this->get(self::CONFIG_PATH_CIRCULAR_LINK_HREF, '#');
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
}
