<?php
declare(strict_types=1);

namespace BetaKiller\Config;

class AppConfig extends AbstractConfig implements AppConfigInterface
{
    /**
     * @return string
     */
    protected function getConfigRootGroup(): string
    {
        return self::CONFIG_GROUP_NAME;
    }

    /**
     * Returns namespace for app-related classes (ifaces, widgets, factories, etc) or NULL if these classes located at root namespace
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->get(['namespace']);
    }

    /**
     * Returns app`s base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return \Kohana::$base_url;
    }

    /**
     * Returns app`s administrator email
     *
     * @return string
     */
    public function getAdminEmail(): string
    {
        $host = parse_url($this->getBaseUrl(), PHP_URL_HOST);

        return 'admin@'.$host;
    }

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function isTrailingSlashEnabled(): bool
    {
        return (bool)$this->get(self::PATH_IS_TRAILING_SLASH_ENABLED);
    }

    /**
     * @return string
     */
    public function getCircularLinkHref(): string
    {
        return (string)$this->get(self::PATH_CIRCULAR_LINK_HREF);
    }

    /**
     * @return bool
     */
    public function isPageCacheEnabled(): bool
    {
        return (bool)$this->get(self::PATH_PAGE_CACHE_ENABLED, false);
    }

    /**
     * @return string
     */
    public function getPageCachePath(): string
    {
        return rtrim($this->get(self::PATH_PAGE_CACHE_PATH), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    /**
     * @return string[]
     */
    public function getAllowedLanguages(): array
    {
        return (array)$this->get(['languages'], ['en']);
    }
}
