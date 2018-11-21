<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\NotImplementedHttpException;

class GuestUser extends User implements GuestUserInterface
{
    /**
     * @param \BetaKiller\Model\AccountStatusInterface $accountStatusModel
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setStatus(AccountStatusInterface $accountStatusModel): UserInterface
    {
        throw new NotImplementedHttpException();
    }

    /**
     * @return \BetaKiller\Model\AccountStatusInterface
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function getStatus(): AccountStatusInterface
    {
        throw new NotImplementedHttpException();
    }

    /**
     * @return bool
     */
    public function isEmailConfirmed(): bool
    {
        throw new NotImplementedHttpException();
    }

    /**
     * @return RoleInterface[]
     */
    public function getAccessControlRoles(): array
    {
        return [
            new Role(['name' => RoleInterface::GUEST]),
        ];
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return 'Guest';
    }

    protected function fetchAllUserRolesNames(): array
    {
        return [
            RoleInterface::GUEST,
        ];
    }
}
