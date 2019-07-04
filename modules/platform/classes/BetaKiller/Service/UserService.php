<?php
namespace BetaKiller\Service;


use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\AbstractTask;

class UserService
{
    public const DEFAULT_IP = '127.0.0.1';

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var \BetaKiller\Repository\RoleRepositoryInterface
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
     * @var \BetaKiller\Factory\EntityFactoryInterface
     */
    private $entityFactory;

    /**
     * UserService constructor.
     *
     * @param \BetaKiller\Factory\EntityFactoryInterface     $entityFactory
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Repository\RoleRepositoryInterface $roleRepo
     * @param \BetaKiller\Factory\GuestUserFactory           $guestFactory
     * @param \BetaKiller\Config\AppConfigInterface          $appConfig
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        UserRepositoryInterface $userRepo,
        RoleRepositoryInterface $roleRepo,
        GuestUserFactory $guestFactory,
        AppConfigInterface $appConfig
    ) {
        $this->entityFactory  = $entityFactory;
        $this->userRepository = $userRepo;
        $this->roleRepository = $roleRepo;
        $this->appConfig      = $appConfig;
        $this->guestFactory   = $guestFactory;
    }

    /**
     * @param string $login
     * @param string $email
     * @param string $createdFromIp
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function createUser(
        string $login,
        string $email,
        string $createdFromIp
    ): UserInterface {
        $basicRoles = [
            $this->roleRepository->getGuestRole(),
            $this->roleRepository->getLoginRole(),
        ];

        /** @var UserInterface $user */
        $user = $this->entityFactory->create(User::getModelName());

        $user
            ->setCreatedAt()
            ->setUsername($login)
            ->setEmail($email)
            ->setCreatedFromIP($createdFromIp);

        // Enable email notifications by default
        $user->enableEmailNotification();

        // Create new model via save so ID will be populated for adding roles
        $this->userRepository->save($user);

        foreach ($basicRoles as $role) {
            $user->addRole($role);
        }

        return $user;
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
     */
    public function isAdmin(UserInterface $user): bool
    {
        // This role is not assigned directly but through inheritance
        return $user->hasRoleName(RoleInterface::ADMIN_PANEL);
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
