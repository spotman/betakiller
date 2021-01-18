<?php
declare(strict_types=1);

namespace BetaKiller\Query;

use BetaKiller\Model\UserInterface;

class NotificationLogQuery
{
    private ?UserInterface $user = null;

    private ?string $messageCodename = null;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Query\NotificationLogQuery
     */
    public function forUser(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Query\NotificationLogQuery
     */
    public function withMessageCodename(string $codename): self
    {
        $this->messageCodename = $codename;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasUserDefined(): bool
    {
        return $this->user !== null;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function hasMessageCodenameDefined(): bool
    {
        return $this->messageCodename !== null;
    }

    /**
     * @return string
     */
    public function getMessageCodename(): string
    {
        return $this->messageCodename;
    }
}
