<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\NotImplementedHttpException;

class GuestUser extends User implements GuestUserInterface
{
    /**
     * @param \BetaKiller\Model\UserStatusInterface $userStatusModel
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setStatus(UserStatusInterface $userStatusModel): UserInterface
    {
        throw new NotImplementedHttpException();
    }

    /**
     * @return \BetaKiller\Model\UserStatusInterface
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function getStatus(): UserStatusInterface
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

    /**
     * @return string
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function getCreatedFromIP(): string
    {
        throw new NotImplementedHttpException();
    }

    /**
     * @param string $ip
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setCreatedFromIP(string $ip): UserInterface
    {
        throw new NotImplementedHttpException();
    }
}
