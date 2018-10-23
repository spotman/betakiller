<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\SupervisorDaemon;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Task\AbstractTask;
use Symfony\Component\Process\Process;

class Ping extends AbstractTask
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * Ping constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;

        parent::__construct();
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        // No options
        return [];
    }

    public function run(): void
    {
        $cmd = self::getTaskCmd($this->appEnv, 'daemon:run', [
            'name' => SupervisorDaemon::CODENAME,
        ], false, true);

        $process = new Process($cmd);

        // Execute supervisor and detach it
        $process
            ->setTimeout(null)
            ->disableOutput()
            ->setIdleTimeout(null)
            ->run();
    }
}
