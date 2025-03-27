<?php
namespace BetaKiller\Config;

use Psr\Http\Message\UriInterface;

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
     * @return \Psr\Http\Message\UriInterface
     */
    public function getBaseUri(): UriInterface;

    /**
     * Returns true if base url is HTTPS-based
     *
     * @return bool
     */
    public function isSecure(): bool;

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function isTrailingSlashEnabled(): bool;

    /**
     * Returns true if redirect on missing pages is allowed
     * @return bool
     */
    public function isRedirectMissingEnabled(): bool;

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
     * @return string[]
     */
    public function getIgnoredQueryParams(): array;

    /**
     * Returns array of FQCN
     *
     * @return string[]
     */
    public function getRawUrlParameters(): array;

    /**
     * @return string
     */
    public function getSupportUrl(): string;
}
