<?php
namespace BetaKiller\Helper;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\DI\Container;

trait ConfigTrait
{
    /**
     * @param string $group
     * @param null $default
     * @return \Config_Group|string|int|bool|null
     * @throws \Kohana_Exception
     */
    private function config($group, $default = NULL)
    {
        /** @var ConfigProviderInterface $config */
        $config = Container::getInstance()->get(ConfigProviderInterface::class);

        $path = explode('.', $group);
        return $config->load($path) ?: $default;
    }
}
