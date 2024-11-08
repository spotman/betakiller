<?php

declare(strict_types=1);

namespace BetaKiller\Config;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class AppConfig extends AbstractConfig implements AppConfigInterface
{
    public const PATH_NAMESPACE                   = ['namespace'];
    public const PATH_BASE_URL                    = ['url', 'base'];
    public const PATH_IS_TRAILING_SLASH_ENABLED   = ['url', 'is_trailing_slash_enabled'];
    public const PATH_IS_REDIRECT_MISSING_ENABLED = ['url', 'is_redirect_missing_enabled'];
    public const PATH_CIRCULAR_LINK_HREF          = ['url', 'circular_link_href'];
    public const PATH_SUPPORT_URL                 = ['url', 'support'];
    public const PATH_QUERY_IGNORED               = ['url', 'query', 'ignored'];
    public const PATH_RAW_PARAMETERS              = ['url', 'parameters'];
    public const PATH_PAGE_CACHE_PATH             = ['cache', 'page', 'path'];
    public const PATH_PAGE_CACHE_ENABLED          = ['cache', 'page', 'enabled'];

    /**
     * @var \Psr\Http\Message\UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \Psr\Http\Message\UriFactoryInterface      $uriFactory
     */
    public function __construct(ConfigProviderInterface $config, UriFactoryInterface $uriFactory)
    {
        parent::__construct($config);

        $this->uriFactory = $uriFactory;
    }

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
        return $this->getString(self::PATH_NAMESPACE);
    }

    /**
     * Returns app`s base URL
     *
     * @return \Psr\Http\Message\UriInterface
     * @throws \BetaKiller\Exception
     */
    public function getBaseUri(): UriInterface
    {
        $url = $this->getString(self::PATH_BASE_URL);

        return $this->uriFactory->createUri($url);
    }

    /**
     * Returns true if base url is HTTPS-based
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->getBaseUri()->getScheme() === 'https';
    }

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function isTrailingSlashEnabled(): bool
    {
        return $this->getBool(self::PATH_IS_TRAILING_SLASH_ENABLED);
    }

    public function isRedirectMissingEnabled(): bool
    {
        return $this->getBool(self::PATH_IS_REDIRECT_MISSING_ENABLED);
    }

    public function getRawUrlParameters(): array
    {
        return $this->getArray(self::PATH_RAW_PARAMETERS);
    }

    /**
     * @return string[]
     */
    public function getIgnoredQueryParams(): array
    {
        return $this->getArray(self::PATH_QUERY_IGNORED, true);
    }

    /**
     * @return string
     */
    public function getCircularLinkHref(): string
    {
        return $this->getString(self::PATH_CIRCULAR_LINK_HREF);
    }

    /**
     * @return bool
     */
    public function isPageCacheEnabled(): bool
    {
        return $this->getBool(self::PATH_PAGE_CACHE_ENABLED);
    }

    /**
     * @return string
     */
    public function getPageCachePath(): string
    {
        return rtrim($this->getString(self::PATH_PAGE_CACHE_PATH), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public function getSupportUrl(): string
    {
        return $this->getString(self::PATH_SUPPORT_URL);
    }
}
