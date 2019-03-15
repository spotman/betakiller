<?php
declare(strict_types=1);

namespace BetaKiller\Config;

class WampConfig extends AbstractConfig implements WampConfigInterface
{
    public const
        CONFIG_GROUP_NAME = 'wamp',
        CONFIG_REALMS = 'realms',
        CONFIG_REALM_KEY_EXT = 'external',
        CONFIG_REALM_KEY_INT = 'internal',

        PATH_REALMS_LIST = [self::CONFIG_REALMS],
        PATH_REALM_EXT = [self::CONFIG_REALMS, self::CONFIG_REALM_KEY_EXT],
        PATH_REALM_INT = [self::CONFIG_REALMS, self::CONFIG_REALM_KEY_INT],
        PATH_CONNECTION_HOST = ['connection', 'host'],
        PATH_CONNECTION_PORT = ['connection', 'port'];

    /**
     * @return string
     */
    protected function getConfigRootGroup(): string
    {
        return self::CONFIG_GROUP_NAME;
    }

    /**
     * @return string[]
     */
    public function getAllRealms(): array
    {
        return (array)$this->get(self::PATH_REALMS_LIST);
    }

    /**
     * @return string
     */
    public function getExternalRealmName(): string
    {
        return (string)$this->get(self::PATH_REALM_EXT);
    }

    /**
     * @return string
     */
    public function getInternalRealmName(): string
    {
        return (string)$this->get(self::PATH_REALM_INT);
    }

    /**
     * @return string
     */
    public function getConnectionHost(): string
    {
        return (string)$this->get(self::PATH_CONNECTION_HOST);
    }

    /**
     * @return string
     */
    public function getConnectionPort(): string
    {
        return (string)$this->get(self::PATH_CONNECTION_PORT);
    }
}
