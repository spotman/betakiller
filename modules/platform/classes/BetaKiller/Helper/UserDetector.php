<?php
namespace BetaKiller\Helper;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Exception;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\AbstractTask;

class UserDetector
{
    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $repository;

    /**
     * @var \BetaKiller\Factory\GuestUserFactory
     */
    private $guestFactory;

    /**
     * UserDetector constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface                $appEnv
     * @param \BetaKiller\Repository\UserRepositoryInterface $repo
     * @param \BetaKiller\Factory\GuestUserFactory           $guestFactory
     */
    public function __construct(AppEnvInterface $appEnv, UserRepositoryInterface $repo, GuestUserFactory $guestFactory)
    {
        $this->appEnv       = $appEnv;
        $this->repository   = $repo;
        $this->guestFactory = $guestFactory;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function detectCliUser(): UserInterface
    {
        // Get username from CLI arguments or use default instead
        $userName = $this->appEnv->getCliOption(AppEnvInterface::CLI_OPTION_USER) ?: AbstractTask::CLI_USER_NAME;

        if ($userName === 'guest') {
            return $this->guestFactory->create();
        }

        $user = $this->repository->searchBy($userName);

        if (!$user) {
            throw new Exception('Missing CLI user ":name", install it with CreateCliUser task', [
                ':name' => $userName,
            ]);
        }

        return $user;
    }
}
