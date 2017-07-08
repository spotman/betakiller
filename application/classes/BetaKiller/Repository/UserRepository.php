<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;

/**
 * Class UserRepository
 *
 * @package BetaKiller\Repository
 * @method UserInterface create()
 * @method UserInterface findById(int $id)
 * @method UserInterface[] getAll()
 * @method User getOrmInstance()
 */
class UserRepository extends AbstractOrmBasedRepository
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepository;

    public function searchBy(string $loginOrEmail): ?UserInterface
    {
        return $this->getOrmInstance()->search_by($loginOrEmail);
    }

    /**
     * @return \Database_Result|UserInterface[]
     */
    public function getDevelopers()
    {
        $role = $this->roleRepository->getDeveloperRole();
        return $this->getUsersWithRole($role);
    }

    /**
     * @return \Database_Result|UserInterface[]
     */
    public function getModerators()
    {
        $role = $this->roleRepository->getModeratorRole();
        return $this->getUsersWithRole($role);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return UserInterface[]
     */
    public function getUsersWithRole(RoleInterface $role): array
    {
        // TODO Deal with roles inheritance (current implementation returns only users with explicit role)
        return $role->get_users()->get_all();
    }

    public function createNewUser(string $login, string $email): UserInterface
    {
        // Generate random password
        $password = md5(microtime());

        $basicRoles = [
            $this->roleRepository->getGuestRole(),
            $this->roleRepository->getLoginRole(),
        ];

        $model = $this->create()
            ->set_username($login)
            ->set_password($password)
            ->set_email($email);

        // Create new model via save so ID will be populated for adding roles
        $this->save($model);

        foreach ($basicRoles as $role) {
            $model->add_role($role);
        }

        return $model;
    }
}
