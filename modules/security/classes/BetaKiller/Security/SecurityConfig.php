<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use BetaKiller\Config\AbstractConfig;

class SecurityConfig extends AbstractConfig implements SecurityConfigInterface
{
    private const PATH_CSP_ENABLED    = ['csp', 'enabled'];
    private const PATH_CSP_SAFE_MODE  = ['csp', 'safe_mode'];
    private const PATH_CSP_RULES      = ['csp', 'rules'];
    private const PATH_HEADERS_ADD    = ['headers', 'add'];
    private const PATH_HEADERS_REMOVE = ['headers', 'remove'];

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
     * @return bool
     */
    public function isCspSafeModeEnabled(): bool
    {
        return (bool)$this->get(self::PATH_CSP_SAFE_MODE);
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
}
