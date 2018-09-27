<?php
declare(strict_types=1);

namespace BetaKiller\Config;

class WampConfig extends AbstractConfig implements WampConfigInterface
{
    public const
        CONFIG_GROUP_NAME = 'wamp',
        PATH_REALM_NAME = ['realmName'],
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
     * @return string
     */
    public function getRealmName(): string
    {
        return (string)$this->get(self::PATH_REALM_NAME);
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
