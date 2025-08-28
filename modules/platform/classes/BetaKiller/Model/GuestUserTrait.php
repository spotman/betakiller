<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Workflow\WorkflowStateInterface;
use DateTimeImmutable;

trait GuestUserTrait
{
    public function isAdmin(): bool
    {
        return false;
    }

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
    public function isBanned(): bool
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
     * @inheritDoc
     */
    public function isEmailConfirmed(): bool
    {
        throw new NotImplementedHttpException();
    }

    /**
     * @inheritDoc
     */
    protected function fetchRoles(): array
    {
        return [
            Role::createFromName(RoleInterface::GUEST),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getID(): string
    {
        return 'guest';
    }

    /**
     * @inheritDoc
     */
    public function getUsername(): string
    {
        return 'guest';
    }

    /**
     * @inheritDoc
     */
    public function getCreatedFromIP(): string
    {
        throw new NotImplementedHttpException();
    }

    /**
     * @inheritDoc
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
