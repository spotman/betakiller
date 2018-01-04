<?php

use BetaKiller\Task\AbstractTask;

class Task_CreateCliUser extends AbstractTask
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepository;

    /**
     * @Inject
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepository;

    /**
     * @Inject
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @param array $params
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \ORM_Validation_Exception
     */
    protected function _execute(array $params): void
    {
        $cliUserName = AbstractTask::CLI_USER_NAME;

        $host  = parse_url($this->appConfig->getBaseUrl(), PHP_URL_HOST);
        $email = $cliUserName.'@'.$host;

        if ($user = $this->userRepository->searchBy($cliUserName)) {
            $this->logger->info('User [:name] already exists, exiting', [
                ':name' => $user->getUsername(),
            ]);

            return;
        }

        $user = $this->userRepository->createNewUser($cliUserName, $email);

        // No notification for cron user
        $user->disableEmailNotification();

        // Allowing everything (admin may remove some roles later if needed)
        $roles = $this->roleRepository->getAll();

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->userRepository->save($user);

        $this->logger->info('User [:name] successfully created', [
            ':name' => $user->getUsername(),
        ]);
    }
}
