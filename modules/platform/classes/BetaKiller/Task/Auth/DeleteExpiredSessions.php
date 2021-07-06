<?php
declare(strict_types=1);

namespace BetaKiller\Task\Auth;

use BetaKiller\Config\SessionConfigInterface;
use BetaKiller\Repository\UserSessionRepositoryInterface;
use BetaKiller\Task\AbstractTask;

class DeleteExpiredSessions extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\SessionConfigInterface
     */
    private SessionConfigInterface $config;

    /**
     * @var \BetaKiller\Repository\UserSessionRepositoryInterface
     */
    private UserSessionRepositoryInterface $sessionRepo;

    /**
     * DeleteExpiredSessions constructor.
     *
     * @param \BetaKiller\Repository\UserSessionRepositoryInterface $sessionRepo
     * @param \BetaKiller\Config\SessionConfig                      $config
     */
    public function __construct(
        UserSessionRepositoryInterface $sessionRepo,
        SessionConfigInterface $config
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
