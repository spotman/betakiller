<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use BetaKiller\Config\AbstractConfig;

class SecurityConfig extends AbstractConfig implements SecurityConfigInterface
{
    private const PATH_CSP_ENABLED = ['csp', 'enabled'];
    private const PATH_CSP_RULES = ['csp', 'rules'];

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
     * @return string[][]
     */
    public function getCspRules(): array
    {
        return (array)$this->get(self::PATH_CSP_RULES, true);
    }
}
