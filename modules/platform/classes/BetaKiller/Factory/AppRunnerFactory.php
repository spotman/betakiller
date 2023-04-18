<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\AppRunnerInterface;
use BetaKiller\CliAppRunner;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\WebAppRunner;

final class AppRunnerFactory implements AppRunnerFactoryInterface
{
    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \BetaKiller\WebAppRunner
     */
    private WebAppRunner $webRunner;

    /**
     * @var \BetaKiller\CliAppRunner
     */
    private CliAppRunner $cliRunner;

    public function __construct(AppEnvInterface $appEnv, WebAppRunner $webRunner, CliAppRunner $cliRunner)
    {
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
