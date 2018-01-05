<?php
namespace BetaKiller\Service;


use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Task\AbstractTask;

class UserService extends AbstractService
{
    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepository;

    /**
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepository;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * UserService constructor.
     *
     * @param \BetaKiller\Repository\UserRepository $userRepo
     * @param \BetaKiller\Repository\RoleRepository $roleRepo
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     */
    public function __construct(UserRepository $userRepo, RoleRepository $roleRepo, AppConfigInterface $appConfig) {
        $this->userRepository = $userRepo;
        $this->roleRepository = $roleRepo;
        $this->appConfig = $appConfig;
    }

    public function createUser(string $login, string $email, ?string $password = null): UserInterface
    {
        // Generate random password if none provided
        $password = $password ?? md5(microtime());

        $basicRoles = [
            $this->roleRepository->getGuestRole(),
            $this->roleRepository->getLoginRole(),
        ];

        $model = $this->userRepository->create()
            ->setUsername($login)
            ->setPassword($password)
            ->setEmail($email);

        // Enable email notifications by default
        $model->enableEmailNotification();

        // Create new model via save so ID will be populated for adding roles
        $this->userRepository->save($model);

        foreach ($basicRoles as $role) {
            $model->addRole($role);
        }

        return $model;
    }

    /**
     * @return \BetaKiller\Model\UserInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \ORM_Validation_Exception
     */
    public function createCliUser(): ?UserInterface
    {
        $cliUserName = AbstractTask::CLI_USER_NAME;

        $host  = parse_url($this->appConfig->getBaseUrl(), PHP_URL_HOST);
        $email = $cliUserName.'@'.$host;

        if ($user = $this->userRepository->searchBy($cliUserName)) {
            return null;
        }

        $user = $this->createUser($cliUserName, $email);

        // No notification for cron user
        $user->disableEmailNotification();

        // Allowing everything (admin may remove some roles later if needed)
        $roles = $this->roleRepository->getAll();

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * @return \Database_Result|UserInterface[]
     */
    public function getDevelopers()
    {
        $role = $this->roleRepository->getDeveloperRole();
        return $this->userRepository->getUsersWithRole($role);
    }

    /**
     * @return \Database_Result|UserInterface[]
     */
    public function getModerators()
    {
        $role = $this->roleRepository->getModeratorRole();
        return $this->userRepository->getUsersWithRole($role);
    }

}
