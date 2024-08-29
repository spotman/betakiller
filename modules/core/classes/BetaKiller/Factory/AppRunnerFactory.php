<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\AppRunnerInterface;
use BetaKiller\CliAppRunnerInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\WebAppRunnerInterface;
use Psr\Container\ContainerInterface;

final readonly class AppRunnerFactory implements AppRunnerFactoryInterface
{
    public function __construct(private AppEnvInterface $appEnv, private ContainerInterface $container)
    {
    }

    public function create(): AppRunnerInterface
    {
        return $this->appEnv->isCli()
            ? $this->createConsoleApp()
            : $this->createWebApp();
    }

    private function createWebApp(): WebAppRunnerInterface
    {
        return $this->container->get(WebAppRunnerInterface::class);
    }

    private function createConsoleApp(): CliAppRunnerInterface
    {
        return $this->container->get(CliAppRunnerInterface::class);
    }
}
