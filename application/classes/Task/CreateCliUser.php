<?php

use BetaKiller\Task\AbstractTask;

class Task_CreateCliUser extends AbstractTask
{
    /**
     * @Inject
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * @param array $params
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \ORM_Validation_Exception
     */
    protected function _execute(array $params): void
    {
        $user = $this->userService->createCliUser();

        if (!$user) {
            $this->logger->info('User [:name] already exists, exiting', [
                ':name' => $user->getUsername(),
            ]);
        } else {
            $this->logger->info('User [:name] successfully created', [
                ':name' => $user->getUsername(),
            ]);
        }
    }
}
