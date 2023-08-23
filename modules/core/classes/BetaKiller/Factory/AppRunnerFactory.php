<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\AppRunnerInterface;
use BetaKiller\CliAppRunnerInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\WebAppRunnerInterface;

final class AppRunnerFactory implements AppRunnerFactoryInterface
{
    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \BetaKiller\WebAppRunnerInterface
     */
    private WebAppRunnerInterface $webRunner;

    /**
     * @var \BetaKiller\CliAppRunnerInterface
     */
    private CliAppRunnerInterface $cliRunner;

    public function __construct(
        AppEnvInterface       $appEnv,
        WebAppRunnerInterface $webRunner,
        CliAppRunnerInterface $cliRunner
    ) {
        $this->appEnv    = $appEnv;
        $this->webRunner = $webRunner;
        $this->cliRunner = $cliRunner;
    }

    public function create(): AppRunnerInterface
    {
        return $this->appEnv->isCli()
            ? $this->cliRunner
            : $this->webRunner;
    }
}
