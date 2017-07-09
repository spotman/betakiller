<?php

namespace BetaKiller\Model;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Status\StatusRelatedModelOrmTrait;
use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Model_ContentCommentStatusTransition;
use Status_Workflow_ContentComment;
use Validation;

class ContentComment extends TreeModelSingleParentOrm
    implements ContentCommentInterface
{
    use OrmBasedEntityRelatedModelTrait;
    use OrmBasedEntityHasWordpressIdTrait;
    use StatusRelatedModelOrmTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function _initialize(): void
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

        $this->initialize_related_model_relation();

        parent::_initialize();
    }

    /**
     * @inheritDoc
     */
    protected function get_workflow_name(): string
    {
        return 'ContentComment';
    }

    /**
     * @inheritDoc
     */
    protected function get_status_relation_model_name(): string
    {
        return 'ContentCommentStatus';
    }

    /**
     * @inheritDoc
     */
    protected function get_status_relation_foreign_key(): string
    {
        return 'status_id';
    }

    /**
     * Place here additional query params
     */
    protected function additionalTreeTraversalFiltering()
    {
        // Nothing to do
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
        if (!$this->get_author_user()) {
            $rules += $guestRules;
        }

        return $rules;
    }

    public function getPublicReadUrl(IFaceHelper $helper): string
    {
        return $this->getRelatedEntityPublicUrl($helper).'#'.$this->getHtmlDomID();
    }

    private function getRelatedEntityPublicUrl(IFaceHelper $helper): string
    {
        $relatedEntity = $this->getRelatedEntityInstance();

        return $helper->getReadEntityUrl($relatedEntity, IFaceZone::PUBLIC_ZONE);
    }

    public function getRelatedContentLabel(): string
    {
        return $this->getRelatedEntityInstance()->getLabel();
    }

    public function getHtmlDomID(): string
    {
        return 'content-comment-'.$this->getID();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_guest_author_email(string $value)
    {
        $this->set('author_email', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function get_guest_author_email(): string
    {
        return $this->get('author_email');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_guest_author_name(string $value)
    {
        $this->set('author_name', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function get_guest_author_name(): string
    {
        return $this->get('author_name');
    }

    public function set_author_user(UserInterface $value = null)
    {
        $this->set('author', $value);

        return $this;
    }

    /**
     * @return UserInterface|null
     */
    public function get_author_user(): ?UserInterface
    {
        /** @var UserInterface $author */
        $author = $this->get('author');

        return $author->loaded() ? $author : null;
    }

    /**
     * @return bool
     */
    public function author_is_guest(): bool
    {
        return !$this->get_author_user();
    }

    public function get_author_name(): string
    {
        $author = $this->get_author_user();

        return $author ? $author->getFirstName() : $this->get_guest_author_name();
    }

    public function get_author_email(): string
    {
        $author = $this->get_author_user();

        return $author ? $author->getEmail() : $this->get_guest_author_email();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_message(string $value)
    {
        $this->set('message', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function get_message(): string
    {
        return $this->get('message');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_ip_address(string $value)
    {
        $this->set('ip_address', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function get_ip_address(): string
    {
        return $this->get('ip_address');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_guest_author_user(string $value)
    {
        $this->set('author_user', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function get_guest_author_user(): string
    {
        return $this->get('author_user');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_user_agent(string $value)
    {
        $this->set('user_agent', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function get_user_agent(): string
    {
        return $this->get('user_agent');
    }

    public function set_created_at(DateTimeInterface $value = null)
    {
        $this->set_datetime_column_value('created_at', $value ?: new DateTimeImmutable);

        return $this;
    }

    public function get_created_at(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('created_at');
    }

    /**
     * @return string
     */
    public function get_path(): string
    {
        return $this->get('path');
    }

    /**
     * @return $this
     */
    protected function set_path()
    {
        $parent     = $this->getParent();
        $parentPath = $parent ? $parent->get_path() : 0;

        $this->set('path', $parentPath.'.'.$this->getID());

        return $this;
    }

    public function get_level(): int
    {
        return substr_count($this->get_path(), '.') - 1;
    }

    /**
     * @return bool
     */
    public function is_pending(): bool
    {
        return $this->get_status_id() === ContentCommentStatus::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function is_approved(): bool
    {
        return $this->get_status_id() === ContentCommentStatus::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function is_spam(): bool
    {
        return $this->get_status_id() === ContentCommentStatus::STATUS_SPAM;
    }

    /**
     * @return bool
     */
    public function is_deleted(): bool
    {
        return $this->get_status_id() === ContentCommentStatus::STATUS_TRASH;
    }

    public function init_as_pending()
    {
        $status = $this->get_status_by_id(ContentCommentStatus::STATUS_PENDING);

        return $this->init_status($status);
    }

    public function init_as_approved()
    {
        $status = $this->get_status_by_id(ContentCommentStatus::STATUS_APPROVED);

        return $this->init_status($status);
    }

    public function init_as_spam()
    {
        $status = $this->get_status_by_id(ContentCommentStatus::STATUS_SPAM);

        return $this->init_status($status);
    }

    public function init_as_trash()
    {
        $status = $this->get_status_by_id(ContentCommentStatus::STATUS_TRASH);

        return $this->init_status($status);
    }


    public function isApproveAllowed(): bool
    {
        return $this->is_status_transition_allowed(Model_ContentCommentStatusTransition::APPROVE);
    }

    public function draft()
    {
        $this->workflow()->draft();

        return $this;
    }

    public function approve()
    {
        $this->workflow()->approve();

        return $this;
    }

    public function reject()
    {
        $this->workflow()->reject();

        return $this;
    }

    public function mark_as_spam()
    {
        $this->workflow()->markAsSpam();

        return $this;
    }

    public function move_to_trash()
    {
        $this->workflow()->moveToTrash();

        return $this;
    }

    public function restore_from_trash()
    {
        $this->workflow()->restoreFromTrash();

        return $this;
    }

    /**
     * @return Status_Workflow_ContentComment|\BetaKiller\Status\StatusWorkflowInterface
     */
    protected function workflow()
    {
        return $this->workflow_factory();
    }

    /**
     * @inheritDoc
     */
    public function create(Validation $validation = null)
    {
        // Preset default status
        if (!$this->has_current_status()) {
            $this->init_as_pending();
        }

        $path_changed = $this->changed('path');

        /** @var ContentComment $obj */
        $obj = parent::create($validation);

        if (!$path_changed) {
            $obj->set_path()->save();
        }

        return $obj;
    }
}
