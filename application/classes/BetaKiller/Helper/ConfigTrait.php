<?php
namespace BetaKiller\Helper;

use BetaKiller\Config\ConfigInterface;
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
        /** @var ConfigInterface $config */
        $config = Container::instance()->get(ConfigInterface::class);

        $path = explode('.', $group);
        return $config->load($path) ?: $default;
    }
}
