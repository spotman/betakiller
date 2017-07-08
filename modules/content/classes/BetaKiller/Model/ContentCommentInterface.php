<?php

namespace BetaKiller\Model;

use BetaKiller\Status\StatusRelatedModelInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentInterface;
use DateTimeImmutable;
use DateTimeInterface;

interface ContentCommentInterface extends TreeModelSingleParentInterface, EntityModelRelatedInterface,
    StatusRelatedModelInterface, EntityHasWordpressIdInterface, HasPublicReadUrlInterface
{
    public function getRelatedContentLabel(): string;

    public function getHtmlDomID(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_guest_author_email(string $value);

    /**
     * @return string
     */
    public function get_guest_author_email(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_guest_author_name(string $value);

    /**
     * @return string
     */
    public function get_guest_author_name(): string;

    public function set_author_user(UserInterface $value = null);

    /**
     * @return UserInterface|null
     */
    public function get_author_user(): ?UserInterface;

    /**
     * @return bool
     */
    public function author_is_guest(): bool;

    public function get_author_name(): string;

    public function get_author_email(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_message(string $value);

    /**
     * @return string
     */
    public function get_message(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_ip_address(string $value);

    /**
     * @return string
     */
    public function get_ip_address(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_guest_author_user(string $value);

    /**
     * @return string
     */
    public function get_guest_author_user(): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_user_agent(string $value);

    /**
     * @return string
     */
    public function get_user_agent(): string;

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return $this
     */
    public function set_created_at(DateTimeInterface $value = null);

    public function get_created_at(): DateTimeImmutable;

    /**
     * @return string
     */
    public function get_path(): string;

    public function get_level(): int;

    /**
     * @return bool
     */
    public function is_pending(): bool;

    /**
     * @return bool
     */
    public function is_approved(): bool;

    /**
     * @return bool
     */
    public function is_spam(): bool;

    /**
     * @return bool
     */
    public function is_deleted(): bool;

    /**
     * @return $this
     */
    public function init_as_pending();

    /**
     * @return $this
     */
    public function init_as_approved();

    /**
     * @return $this
     */
    public function init_as_spam();

    /**
     * @return $this
     */
    public function init_as_trash();

    public function isApproveAllowed(): bool;

    /**
     * @return $this
     */
    public function draft();

    /**
     * @return $this
     */
    public function approve();

    /**
     * @return $this
     */
    public function reject();

    /**
     * @return $this
     */
    public function mark_as_spam();

    /**
     * @return $this
     */
    public function move_to_trash();

    /**
     * @return $this
     */
    public function restore_from_trash();
}
