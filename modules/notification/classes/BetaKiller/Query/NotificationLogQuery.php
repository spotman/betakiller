<?php

declare(strict_types=1);

namespace BetaKiller\Query;

use BetaKiller\Model\UserInterface;

class NotificationLogQuery
{
    /**
     * @var \BetaKiller\Model\UserInterface|null
     */
    private ?UserInterface $user = null;

    private ?string $targetIdentity = null;

    /**
     * @var string|null
     */
    private ?string $messageCodename = null;

    /**
     * @var string|null
     */
    private ?string $status = null;

    /**
     * @var string|null
     */
    private ?string $transport = null;

    public function forTargetIdentity(string $identity): self
    {
        $this->targetIdentity = $identity;

        return $this;
    }

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
    public function hasTargetDefined(): bool
    {
        return $this->targetIdentity !== null;
    }

    public function getTargetIdentity(): string
    {
        return $this->targetIdentity;
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

    /**
     * @param string $codename
     *
     * @return $this
     */
    public function withStatus(string $codename): self
    {
        $this->status = $codename;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasStatusDefined(): bool
    {
        return $this->status !== null;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $codename
     *
     * @return $this
     */
    public function throughTransport(string $codename): self
    {
        $this->transport = $codename;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasTransportDefined(): bool
    {
        return $this->transport !== null;
    }

    /**
     * @return string
     */
    public function getTransport(): string
    {
        return $this->transport;
    }
}
