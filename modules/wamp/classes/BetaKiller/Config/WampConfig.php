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
        PATH_CLIENT_HOST = ['client', 'host'],
        PATH_CLIENT_PORT = ['client', 'port'],
        PATH_SERVER_HOST = ['server', 'host'],
        PATH_SERVER_PORT = ['server', 'port'];

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
    public function getClientHost(): string
    {
        return (string)$this->get(self::PATH_CLIENT_HOST);
    }

    /**
     * @return string
     */
    public function getClientPort(): string
    {
        return (string)$this->get(self::PATH_CLIENT_PORT);
    }

    /**
     * @inheritDoc
     */
    public function hasServerHost(): bool
    {
        return (bool)$this->get(self::PATH_SERVER_HOST);
    }

    /**
     * @inheritDoc
     */
    public function getServerHost(): string
    {
        return (string)$this->get(self::PATH_SERVER_HOST);
    }

    /**
     * @inheritDoc
     */
    public function getServerPort(): string
    {
        return (string)$this->get(self::PATH_SERVER_PORT);
    }
}
