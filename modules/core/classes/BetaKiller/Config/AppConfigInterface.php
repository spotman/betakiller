<?php
namespace BetaKiller\Config;


interface AppConfigInterface
{
    public const CONFIG_GROUP_NAME              = 'app';
    public const PATH_PAGE_CACHE_PATH           = ['cache', 'page', 'path'];
    public const PATH_CIRCULAR_LINK_HREF        = ['url', 'circular_link_href'];
    public const PATH_IS_TRAILING_SLASH_ENABLED = ['url', 'is_trailing_slash_enabled'];
    public const PATH_PAGE_CACHE_ENABLED        = ['cache', 'page', 'enabled'];

    /**
     * Returns namespace for app-related classes (ifaces, widgets, factories, etc) or NULL if these classes located at root namespace
     *
     * @return string
     */
    public function getNamespace(): string;

    /**
     * Returns app`s base URL
     *
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * Returns app`s administrator email
     *
     * @return string
     */
    public function getAdminEmail(): string;

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function isTrailingSlashEnabled(): bool;

    /**
     * @return string
     */
    public function getCircularLinkHref(): string;

    /**
     * @return bool
     */
    public function isPageCacheEnabled(): bool;

    /**
     * @return string
     */
    public function getPageCachePath(): string;

    /**
     * First language is primary one
     *
     * @return string[]
     */
    public function getAllowedLanguages(): array;
}
