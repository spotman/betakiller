<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\AbstractTask;

class UserDetector
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $repository;

    /**
     * UserDetector constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface             $appEnv
     * @param \BetaKiller\Repository\UserRepositoryInterface $repo
     */
    public function __construct(AppEnvInterface $appEnv, UserRepositoryInterface $repo)
    {
        $this->appEnv     = $appEnv;
        $this->repository = $repo;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function detectCliUser(): UserInterface
    {
        // Get username from CLI arguments or use default instead
        $userName = $this->appEnv->getCliOption('user') ?: AbstractTask::CLI_USER_NAME;

        $user = $this->repository->searchBy($userName);

        if (!$user) {
            throw new Exception('Missing CLI user ":name", install it with CreateCliUser task', [
                ':name' => $userName,
            ]);
        }

        return $user;
    }
}
