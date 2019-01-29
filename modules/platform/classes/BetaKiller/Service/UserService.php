<?php
namespace BetaKiller\Service;


use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Task\AbstractTask;

class UserService
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
     * @var \BetaKiller\Factory\GuestUserFactory
     */
    private $guestFactory;

    /**
     * UserService constructor.
     *
     * @param \BetaKiller\Repository\UserRepository $userRepo
     * @param \BetaKiller\Repository\RoleRepository $roleRepo
     * @param \BetaKiller\Factory\GuestUserFactory  $guestFactory
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     */
    public function __construct(
        UserRepository $userRepo,
        RoleRepository $roleRepo,
        GuestUserFactory $guestFactory,
        AppConfigInterface $appConfig
    ) {
        $this->userRepository = $userRepo;
        $this->roleRepository = $roleRepo;
        $this->appConfig      = $appConfig;
        $this->guestFactory   = $guestFactory;
    }

    /**
     * @param string      $login
     * @param string      $email
     * @param string      $createdFromIp
     * @param null|string $password
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function createUser(
        string $login,
        string $email,
        string $createdFromIp,
        ?string $password = null
    ): UserInterface
    {
        // Generate random password if none provided
        $password = $password ?? md5(microtime());

        $basicRoles = [
            $this->roleRepository->getGuestRole(),
            $this->roleRepository->getLoginRole(),
        ];

        $model = $this->userRepository->create()
            ->setCreatedAt()
            ->setUsername($login)
            ->setPassword($password)
            ->setEmail($email)
            ->setCreatedFromIP($createdFromIp);

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
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Exception\ValidationException
     */
    public function createCliUser(): UserInterface
    {
        $cliUserName = AbstractTask::CLI_USER_NAME;

        $host  = $this->appConfig->getBaseUri()->getHost();
        $email = $cliUserName.'@'.$host;

        $user = $this->userRepository->searchBy($cliUserName);

        if (!$user) {
            $user = $this->createUser($cliUserName, $email, '8.8.8.8')
                ->setFirstName('Minion')
                ->setLastName('Server');
        }

        // No notification for cron user
        $user->disableEmailNotification();

        // Allow everything (admin may remove some roles later if needed)
        foreach ($this->roleRepository->getAll() as $role) {
            if (!$user->hasRole($role)) {
                $user->addRole($role);
            }
        }

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getDevelopers(): array
    {
        $role = $this->roleRepository->getDeveloperRole();

        return $this->userRepository->getUsersWithRole($role);
    }

    /**
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getModerators(): array
    {
        $role = $this->roleRepository->getModeratorRole();

        return $this->userRepository->getUsersWithRole($role);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function isDeveloper(UserInterface $user): bool
    {
        return $user->hasRole($this->roleRepository->getDeveloperRole());
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function isModerator(UserInterface $user): bool
    {
        return $user->hasRole($this->roleRepository->getModeratorRole());
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function isAdmin(UserInterface $user): bool
    {
        return $user->hasRole($this->roleRepository->getAdminPanelRole());
    }

    /**
     * @return \BetaKiller\Model\GuestUserInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createGuest(): GuestUserInterface
    {
        return $this->guestFactory->create();
    }
}
