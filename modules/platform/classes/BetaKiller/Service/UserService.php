<?php
namespace BetaKiller\Service;


use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\RoleInterface;
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
    private UserRepositoryInterface $userRepository;

    /**
     * @var \BetaKiller\Repository\RoleRepositoryInterface
     */
    private RoleRepositoryInterface $roleRepository;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private AppConfigInterface $appConfig;

    /**
     * @var \BetaKiller\Factory\GuestUserFactory
     */
    private GuestUserFactory $guestFactory;

    /**
     * @var \BetaKiller\Workflow\UserWorkflow
     */
    private UserWorkflow $workflow;

    /**
     * UserService constructor.
     *
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Repository\RoleRepositoryInterface $roleRepo
     * @param \BetaKiller\Factory\GuestUserFactory           $guestFactory
     * @param \BetaKiller\Config\AppConfigInterface          $appConfig
     * @param \BetaKiller\Workflow\UserWorkflow              $workflow
     */
    public function __construct(
        UserRepositoryInterface $userRepo,
        RoleRepositoryInterface $roleRepo,
        GuestUserFactory $guestFactory,
        AppConfigInterface $appConfig,
        UserWorkflow $workflow
    ) {
        $this->userRepository = $userRepo;
        $this->roleRepository = $roleRepo;
        $this->appConfig      = $appConfig;
        $this->guestFactory   = $guestFactory;
        $this->workflow       = $workflow;
    }

    /**
     * @param string      $primaryRoleName
     * @param string      $email
     * @param string      $createdFromIp
     *
     * @param string|null $username
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function createUser(
        string $primaryRoleName,
        string $email,
        string $createdFromIp,
        string $username = null
    ): UserInterface {
        return $this->workflow->create($email, $primaryRoleName, $createdFromIp, $username);
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function createCliUser(): UserInterface
    {
        $userName = AbstractTask::CLI_USER_NAME;
        $user     = $this->userRepository->searchBy($userName);

        if (!$user) {
            $host  = $this->appConfig->getBaseUri()->getHost();
            $email = $userName.'@'.$host;

            $user = $this->createUser(RoleInterface::CLI, $email, self::DEFAULT_IP, $userName)
                ->setFirstName('Minion')
                ->setLastName('Server');
        }

        // No notification for cron user
        $user->disableEmailNotification();

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
