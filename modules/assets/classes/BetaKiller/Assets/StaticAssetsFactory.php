<?php
declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Env\AppEnvInterface;
use Psr\Container\ContainerInterface;

class StaticAssetsFactory
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * StaticAssetsFactory constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(): StaticAssets
    {
        $appEnv = $this->container->get(AppEnvInterface::class);
        $config = $this->container->get(AssetsConfig::class);

        return new StaticAssets($appEnv, $config);
    }
}
