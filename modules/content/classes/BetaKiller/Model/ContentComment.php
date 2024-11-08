<?php

namespace BetaKiller\Model;

use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Url\Zone;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

/**
 * Class ContentComment
 *
 * @package BetaKiller\Model
 */
class ContentComment extends AbstractOrmBasedSingleParentTreeModel implements ContentCommentInterface
{
    use OrmBasedEntityItemRelatedModelTrait;
    use OrmBasedEntityHasWordpressIdTrait;
    use HasWorkflowStateOrmModelTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     * @throws Exception
     */
    protected function configure(): void
    {
        $this->_table_name = 'content_comments';

        $this->belongs_to([
            'entity' => [
                'model'       => 'Entity',
                'foreign_key' => 'entity_id',
            ],
            'author' => [
                'model'       => 'User',
                'foreign_key' => 'author_user',
            ],
        ]);

        $this->configureWorkflowStateRelation();

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    public static function getWorkflowStateModelName(): string
    {
        return ContentCommentState::getModelName();
    }

    /**
     * @inheritDoc
     */
    public static function getWorkflowStateForeignKey(): string
    {
        return 'status_id';
    }

    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'entity_id'      => [
                ['not_empty'],
            ],
            'entity_item_id' => [
                ['not_empty'],
            ],
            'ip_address'     => [
                ['not_empty'],
            ],
            'user_agent'     => [
                ['not_empty'],
            ],
            'status_id'      => [
                ['not_empty'],
            ],
            'message'        => [
                ['not_empty'],
                ['max_length', [':value', 4096]],
            ],
            'path'           => [
                ['not_empty'],
            ],
        ];

        $guestRules = [
            'author_name'  => [
                ['not_empty'],
            ],
            'author_email' => [
                ['not_empty'],
                ['email'],
            ],
        ];

        // Additional check for guest fields if no user was set
        if (!$this->getAuthorUser()) {
            $rules += $guestRules;
        }

        return $rules;
    }

    public function getPublicReadUrl(\BetaKiller\Helper\UrlHelperInterface $helper): string
    {
        return $this->getRelatedEntityPublicUrl($helper).'#'.$this->getHtmlDomID();
    }

    /**
     * @param \BetaKiller\Helper\UrlHelperInterface $helper
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function getRelatedEntityPublicUrl(UrlHelperInterface $helper): string
    {
        $relatedEntity = $this->getRelatedEntityInstance();

        return $helper->getReadEntityUrl($relatedEntity, Zone::Public);
    }

    /**
     * @return string
     */
    public function getRelatedContentLabel(): string
    {
        return $this->getRelatedEntityInstance()->getLabel();
    }

    /**
     * @return string
     */
    public function getHtmlDomID(): string
    {
        return 'content-comment-'.$this->getID();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setGuestAuthorEmail(string $value): ContentCommentInterface
    {
        $this->set('author_email', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getGuestAuthorEmail(): string
    {
        return $this->get('author_email');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setGuestAuthorName(string $value): ContentCommentInterface
    {
        $this->set('author_name', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getGuestAuthorName(): string
    {
        return $this->get('author_name');
    }

    public function setAuthorUser(UserInterface $value = null): ContentCommentInterface
    {
        $this->set('author', $value);

        return $this;
    }

    /**
     * @return UserInterface|null
     */
    public function getAuthorUser(): ?UserInterface
    {
        /** @var UserInterface $author */
        $author = $this->get('author');

        return $author->loaded() ? $author : null;
    }

    /**
     * @return bool
     */
    public function authorIsGuest(): bool
    {
        return !$this->getAuthorUser();
    }

    /**
     * @return string
     */
    public function getAuthorName(): string
    {
        $author = $this->getAuthorUser();

        return $author ? $author->getFirstName() : $this->getGuestAuthorName();
    }

    /**
     * @return string
     */
    public function getAuthorEmail(): string
    {
        $author = $this->getAuthorUser();

        return $author ? $author->getEmail() : $this->getGuestAuthorEmail();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMessage(string $value): ContentCommentInterface
    {
        $this->set('message', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->get('message');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setIpAddress(string $value): ContentCommentInterface
    {
        $this->set('ip_address', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->get('ip_address');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setGuestAuthorUser(string $value): ContentCommentInterface
    {
        $this->set('author_user', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getGuestAuthorUser(): string
    {
        return $this->get('author_user');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setUserAgent(string $value): ContentCommentInterface
    {
        $this->set('user_agent', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->get('user_agent');
    }

    public function setCreatedAt(DateTimeInterface $value = null): ContentCommentInterface
    {
        $this->set_datetime_column_value('created_at', $value ?: new DateTimeImmutable);

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('created_at');
    }

    /**
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->get('path');
    }

    /**
     * @param string $value
     *
     * @return $this
     * @throws \Kohana_Exception
     */
    public function setPath(string $value): ContentCommentInterface
    {
        $this->set('path', $value);

        return $this;
    }

    public function getLevel(): int
    {
        return substr_count($this->getPath(), '.') - 1;
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->isInWorkflowState(ContentCommentState::PENDING);
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->isInWorkflowState(ContentCommentState::APPROVED);
    }

    /**
     * @return bool
     */
    public function isSpam(): bool
    {
        return $this->isInWorkflowState(ContentCommentState::SPAM);
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isInWorkflowState(ContentCommentState::TRASH);
    }
}
