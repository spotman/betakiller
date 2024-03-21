<?php
namespace BetaKiller\Service;


use BetaKiller\Factory\UserInfo;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RoleRepositoryInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Workflow\UserWorkflow;

class UserService
{
    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepository;

    /**
     * @var \BetaKiller\Repository\RoleRepositoryInterface
     */
    private RoleRepositoryInterface $roleRepository;

    /**
     * @var \BetaKiller\Workflow\UserWorkflow
     */
    private UserWorkflow $workflow;

    /**
     * UserService constructor.
     *
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Repository\RoleRepositoryInterface $roleRepo
     * @param \BetaKiller\Workflow\UserWorkflow              $workflow
     */
    public function __construct(
        UserRepositoryInterface $userRepo,
        RoleRepositoryInterface $roleRepo,
        UserWorkflow            $workflow
    ) {
        $this->userRepository = $userRepo;
        $this->roleRepository = $roleRepo;
        $this->workflow       = $workflow;
    }

    /**
     * @param string      $primaryRoleName
     * @param string      $createdFromIp
     * @param string      $email
     *
     * @param string|null $username
     * @param string|null $password
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception\DomainException
     */
    public function createUser(
        string $primaryRoleName,
        string $createdFromIp,
        string $email,
        string $username = null,
        string $password = null
    ): UserInterface {
        $info = new UserInfo($createdFromIp, $email, null, $username, $password, $primaryRoleName);

        return $this->workflow->create($info);
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
}
