<?php
declare(strict_types=1);

namespace BetaKiller\Task\Auth;

use BetaKiller\Auth\SessionConfig;
use BetaKiller\Repository\UserSessionRepository;
use BetaKiller\Task\AbstractTask;

class DeleteExpiredSessions extends AbstractTask
{
    /**
     * @var \BetaKiller\Auth\SessionConfig
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\UserSessionRepository
     */
    private $sessionRepo;

    /**
     * DeleteExpiredSessions constructor.
     *
     * @param \BetaKiller\Auth\SessionConfig               $config
     * @param \BetaKiller\Repository\UserSessionRepository $sessionRepo
     */
    public function __construct(
        UserSessionRepository $sessionRepo,
        SessionConfig $config
    ) {
        $this->sessionRepo = $sessionRepo;
        $this->config      = $config;

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
        return [];
    }

    public function run(): void
    {
        $interval = $this->config->getLifetime();

        // Do garbage collection
        foreach ($this->sessionRepo->getExpiredSessions($interval) as $session) {
            $this->sessionRepo->delete($session);
        }
    }
}
