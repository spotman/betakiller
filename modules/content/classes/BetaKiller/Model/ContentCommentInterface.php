<?php

namespace BetaKiller\Model;

use BetaKiller\Workflow\HasWorkflowStateInterface;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Interface ContentCommentInterface
 *
 * @package BetaKiller\Model
 */
interface ContentCommentInterface extends SingleParentTreeModelInterface, EntityItemRelatedInterface,
    HasWorkflowStateInterface, EntityHasWordpressIdInterface, HasPublicReadUrlInterface, DispatchableEntityInterface
{
    public function getRelatedContentLabel(): string;

    public function getHtmlDomID(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setGuestAuthorEmail(string $value);

    /**
     * @return string
     */
    public function getGuestAuthorEmail(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setGuestAuthorName(string $value);

    /**
     * @return string
     */
    public function getGuestAuthorName(): string;

    public function setAuthorUser(UserInterface $value = null);

    /**
     * @return UserInterface|null
     */
    public function getAuthorUser(): ?UserInterface;

    /**
     * @return bool
     */
    public function authorIsGuest(): bool;

    public function getAuthorName(): string;

    public function getAuthorEmail(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMessage(string $value);

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setIpAddress(string $value);

    /**
     * @return string
     */
    public function getIpAddress(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setGuestAuthorUser(string $value);

    /**
     * @return string
     */
    public function getGuestAuthorUser(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setUserAgent(string $value);

    /**
     * @return string
     */
    public function getUserAgent(): string;

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return $this
     */
    public function setCreatedAt(DateTimeInterface $value = null);

    public function getCreatedAt(): DateTimeImmutable;

    /**
     * @return string|null
     */
    public function getPath(): ?string;

    /**
     * @param string $value
     *
     * @return $this
     * @throws \Kohana_Exception
     */
    public function setPath(string $value);

    public function getLevel(): int;

    /**
     * @return bool
     */
    public function isPending(): bool;

    /**
     * @return bool
     */
    public function isApproved(): bool;

    /**
     * @return bool
     */
    public function isSpam(): bool;

    /**
     * @return bool
     */
    public function isDeleted(): bool;

    /**
     * @return $this
     */
    public function initAsPending();

    /**
     * @return $this
     */
    public function initAsApproved();

    /**
     * @return $this
     */
    public function initAsSpam();

    /**
     * @return $this
     */
    public function initAsTrash();

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isApproveAllowed(UserInterface $user): bool;
}
