<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use BetaKiller\Config\AbstractConfig;

class SecurityConfig extends AbstractConfig implements SecurityConfigInterface
{
    private const PATH_CSP_ENABLED   = ['csp', 'enabled'];
    private const PATH_CSP_REPORT    = ['csp', 'report'];
    private const PATH_CSP_SAFE_MODE = ['csp', 'safe_mode'];
    private const PATH_CSP_ERRORS    = ['csp', 'errors'];
    private const PATH_CSP_RULES     = ['csp', 'rules'];

    private const PATH_HSTS_ENABLED    = ['hsts', 'enabled'];
    private const PATH_HSTS_MAX_AGE    = ['hsts', 'max_age'];
    private const PATH_HSTS_SUBDOMAINS = ['hsts', 'subdomains'];
    private const PATH_HSTS_PRELOAD    = ['hsts', 'preload'];

    private const PATH_HEADERS_ADD    = ['headers', 'add'];
    private const PATH_HEADERS_REMOVE = ['headers', 'remove'];

    private const PATH_COOKIES_PROTECTED = ['cookies', 'protected'];

    protected function getConfigRootGroup(): string
    {
        return 'security';
    }

    /**
     * @return bool
     */
    public function isCspEnabled(): bool
    {
        return (bool)$this->get(self::PATH_CSP_ENABLED);
    }


    /**
     * @inheritDoc
     */
    public function isCspReportEnabled(): bool
    {
        return (bool)$this->get(self::PATH_CSP_REPORT);
    }

    /**
     * @return bool
     */
    public function isCspSafeModeEnabled(): bool
    {
        return (bool)$this->get(self::PATH_CSP_SAFE_MODE);
    }

    /**
     * @return bool
     */
    public function isErrorLogEnabled(): bool
    {
        return (bool)$this->get(self::PATH_CSP_ERRORS);
    }

    /**
     * @return string[][]
     */
    public function getCspRules(): array
    {
        return (array)$this->get(self::PATH_CSP_RULES, true);
    }

    /**
     * @return string[]
     */
    public function getHeadersToAdd(): array
    {
        return (array)$this->get(self::PATH_HEADERS_ADD, true);
    }

    /**
     * @return string[]
     */
    public function getHeadersToRemove(): array
    {
        return (array)$this->get(self::PATH_HEADERS_REMOVE, true);
    }

    /**
     * @inheritDoc
     */
    public function isHstsEnabled(): bool
    {
        return (bool)$this->get(self::PATH_HSTS_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function getHstsMaxAge(): int
    {
        return (int)$this->get(self::PATH_HSTS_MAX_AGE);
    }

    /**
     * @return bool
     */
    public function isHstsForSubdomains(): bool
    {
        return (bool)$this->get(self::PATH_HSTS_SUBDOMAINS);
    }

    /**
     * @return bool
     */
    public function isHstsPreload(): bool
    {
        return (bool)$this->get(self::PATH_HSTS_PRELOAD);
    }

    public function getProtectedCookies(): array
    {
        return $this->get(self::PATH_COOKIES_PROTECTED) ?? [];
    }
}
