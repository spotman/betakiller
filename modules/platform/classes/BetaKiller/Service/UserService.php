<?php
namespace BetaKiller\Service;


use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Factory\EntityFactoryInterface;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Workflow\UserWorkflow;

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
     * @var \BetaKiller\Workflow\UserWorkflow
     */
    private $workflow;

    /**
     * UserService constructor.
     *
     * @param \BetaKiller\Factory\EntityFactoryInterface     $entityFactory
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Repository\RoleRepositoryInterface $roleRepo
     * @param \BetaKiller\Factory\GuestUserFactory           $guestFactory
     * @param \BetaKiller\Config\AppConfigInterface          $appConfig
     * @param \BetaKiller\Workflow\UserWorkflow              $workflow
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        UserRepositoryInterface $userRepo,
        RoleRepositoryInterface $roleRepo,
        GuestUserFactory $guestFactory,
        AppConfigInterface $appConfig,
        UserWorkflow $workflow
    ) {
        $this->entityFactory  = $entityFactory;
        $this->userRepository = $userRepo;
        $this->roleRepository = $roleRepo;
        $this->appConfig      = $appConfig;
        $this->guestFactory   = $guestFactory;
        $this->workflow       = $workflow;
    }

    /**
     * @param string      $email
     * @param string      $createdFromIp
     *
     * @param string|null $username
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function createUser(
        string $email,
        string $createdFromIp,
        string $username = null
    ): UserInterface {
        $basicRoles = [
            $this->roleRepository->getGuestRole(),
            $this->roleRepository->getLoginRole(),
        ];

        /** @var UserInterface $user */
        $user = $this->entityFactory->create(User::getModelName());

        $user
            ->setCreatedAt()
            ->setEmail($email)
            ->setCreatedFromIP($createdFromIp);

        if ($username) {
            $user->setUsername($username);
        }

        // Enable email notifications by default
        $user->enableEmailNotification();

        $this->workflow->justCreated($user);

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
     */
    public function createCliUser(): UserInterface
    {
        $cliUserName = AbstractTask::CLI_USER_NAME;

        $host  = $this->appConfig->getBaseUri()->getHost();
        $email = $cliUserName.'@'.$host;

        $user = $this->userRepository->searchBy($cliUserName);

        if (!$user) {
            $user = $this->createUser($email, '8.8.8.8', $cliUserName)
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
     * @return \BetaKiller\Model\GuestUserInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createGuest(): GuestUserInterface
    {
        return $this->guestFactory->create();
    }
}
