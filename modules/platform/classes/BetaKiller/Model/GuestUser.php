<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Workflow\WorkflowStateInterface;
use DateTimeImmutable;

class GuestUser extends User implements GuestUserInterface
{
    /**
     * @inheritDoc
     */
    public function hasWorkflowState(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getWorkflowState(): WorkflowStateInterface
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
     * @inheritDoc
     */
    public function getID(): string
    {
        return 'Guest';
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

    /**
     * @inheritDoc
     */
    public function getLastLoggedIn(): ?DateTimeImmutable
    {
        throw new NotImplementedHttpException();
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(): LanguageInterface
    {
        throw new NotImplementedHttpException();
    }
}
