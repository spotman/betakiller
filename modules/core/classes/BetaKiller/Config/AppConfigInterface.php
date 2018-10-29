<?php
namespace BetaKiller\Config;


interface AppConfigInterface
{
    public const CONFIG_GROUP_NAME = 'app';

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
