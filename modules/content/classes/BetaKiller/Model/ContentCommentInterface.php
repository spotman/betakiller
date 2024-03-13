<?php

namespace BetaKiller\Model;

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
     * @return ContentCommentInterface
     */
    public function setGuestAuthorEmail(string $value): ContentCommentInterface;

    /**
     * @return string
     */
    public function getGuestAuthorEmail(): string;

    /**
     * @param string $value
     *
     * @return ContentCommentInterface
     */
    public function setGuestAuthorName(string $value): ContentCommentInterface;

    /**
     * @return string
     */
    public function getGuestAuthorName(): string;

    /**
     * @param \BetaKiller\Model\UserInterface|null $value
     *
     * @return \BetaKiller\Model\ContentCommentInterface
     */
    public function setAuthorUser(UserInterface $value = null): ContentCommentInterface;

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
     * @return ContentCommentInterface
     */
    public function setMessage(string $value): ContentCommentInterface;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $value
     *
     * @return ContentCommentInterface
     */
    public function setIpAddress(string $value): ContentCommentInterface;

    /**
     * @return string
     */
    public function getIpAddress(): string;

    /**
     * @param string $value
     *
     * @return ContentCommentInterface
     */
    public function setGuestAuthorUser(string $value): ContentCommentInterface;

    /**
     * @return string
     */
    public function getGuestAuthorUser(): string;

    /**
     * @param string $value
     *
     * @return ContentCommentInterface
     */
    public function setUserAgent(string $value): ContentCommentInterface;

    /**
     * @return string
     */
    public function getUserAgent(): string;

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return ContentCommentInterface
     */
    public function setCreatedAt(DateTimeInterface $value = null): ContentCommentInterface;

    public function getCreatedAt(): DateTimeImmutable;

    /**
     * @return string|null
     */
    public function getPath(): ?string;

    /**
     * @param string $value
     *
     * @return ContentCommentInterface
     */
    public function setPath(string $value): ContentCommentInterface;

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
}
