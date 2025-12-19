<?php

declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\RequestUserInterface;

class EntityNotAllowedException extends UrlElementException
{
    public function __construct(
        private readonly DispatchableEntityInterface $entity,
        private readonly string $action,
        private readonly UrlElementInterface $element,
        private readonly RequestUserInterface $user
    ) {
        parent::__construct('Entity ":name" is not allowed to User ":who" with action ":action"', [
            ':name'   => $entity::getUrlContainerKey(),
            ':action' => $action,
            ':who'    => $user->isGuest() ? 'Guest' : $user->getID(),
        ]);
    }

    public function getEntity(): DispatchableEntityInterface
    {
        return $this->entity;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getUrlElement(): UrlElementInterface
    {
        return $this->element;
    }

    public function getUser(): RequestUserInterface
    {
        return $this->user;
    }
}
