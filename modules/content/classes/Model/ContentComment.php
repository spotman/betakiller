<?php

use BetaKiller\Content\ContentRelatedInterface;
use BetaKiller\Content\ImportedFromWordpressInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;
use BetaKiller\Model\UserInterface;
use BetaKiller\Status\StatusRelatedModelOrmTrait;
use BetaKiller\Status\StatusRelatedModelInterface;

class Model_ContentComment extends TreeModelSingleParentOrm
    implements ContentRelatedInterface, StatusRelatedModelInterface, ImportedFromWordpressInterface
{
    use Model_ORM_ContentRelatedTrait;
    use Model_ORM_ImportedFromWordpressTrait;
    use StatusRelatedModelOrmTrait;

    protected $_table_name = 'content_comments';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->belongs_to([
            'entity'            =>  [
                'model'         =>  'ContentEntity',
                'foreign_key'   =>  'entity_id',
            ],
            'author'            =>  [
                'model'         =>  'User',
                'foreign_key'   =>  'author_user',
            ],
        ]);

        $this->initialize_related_model_relation();

        parent::_initialize();
    }

    /**
     * @inheritDoc
     */
    protected function get_workflow_name()
    {
        return 'ContentComment';
    }

    /**
     * @inheritDoc
     */
    protected function get_status_relation_model_name()
    {
        return 'ContentCommentStatus';
    }

    /**
     * @inheritDoc
     */
    protected function get_status_relation_foreign_key()
    {
        return 'status_id';
    }

    /**
     * Place here additional query params
     */
    protected function additional_tree_model_filtering()
    {
        // Nothing to do
    }

    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'entity_id'   =>  [
                ['not_empty'],
            ],
            'entity_item_id'   =>  [
                ['not_empty'],
            ],
            'ip_address'   =>  [
                ['not_empty'],
            ],
            'user_agent'   =>  [
                ['not_empty'],
            ],
            'status_id'   =>  [
                ['not_empty'],
            ],
            'message'   =>  [
                ['not_empty'],
            ],
        ];

        $guestRules = [
            'author_name'   =>  [
                ['not_empty'],
            ],
            'author_email'   =>  [
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

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_guest_author_email($value)
    {
        $this->set('author_email', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function get_guest_author_email()
    {
        return $this->get('author_email');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_guest_author_name($value)
    {
        $this->set('author_name', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function get_guest_author_name()
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
    public function get_author_user()
    {
        /** @var UserInterface $author */
        $author = $this->get('author');
        return $author->loaded() ? $author : null;
    }
    
    public function get_author_name()
    {
        $author = $this->get_author_user();
        return $author ? $author->get_first_name() : $this->get_guest_author_name();
    }

    public function get_author_email()
    {
        $author = $this->get_author_user();
        return $author ? $author->get_email() : $this->get_guest_author_email();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_message($value)
    {
        $this->set('message', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function get_message()
    {
        return $this->get('message');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_ip_address($value)
    {
        $this->set('ip_address', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function get_ip_address()
    {
        return $this->get('ip_address');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_guest_author_user($value)
    {
        $this->set('author_user', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function get_guest_author_user()
    {
        return $this->get('author_user');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_user_agent($value)
    {
        $this->set('user_agent', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function get_user_agent()
    {
        return $this->get('user_agent');
    }

    public function set_created_at(DateTime $value)
    {
        $this->set_datetime_column_value('created_at', $value);
        return $this;
    }

    public function get_created_at()
    {
        return $this->get_datetime_column_value('created_at');
    }

    public function order_by_created_at($asc = false)
    {
        return $this->order_by('created_at', $asc ? 'asc' : 'desc');
    }

    /**
     * @return string
     */
    public function get_path()
    {
        return $this->get('path');
    }

    /**
     * @return $this
     */
    protected function set_path()
    {
        $parent = $this->get_parent();
        $parentPath = $parent ? $parent->get_path() : 0;

        $this->set('path', $parentPath.'.'.$this->get_id());
        return $this;
    }

    /**
     * @return $this
     */
    public function order_by_path()
    {
        return $this->order_by('path', 'asc');
    }

    public function get_level()
    {
        return substr_count($this->get_path(), '.') - 1;
    }

    /**
     * @param \DateInterval $interval
     *
     * @return $this
     */
    public function filter_last_records(DateInterval  $interval)
    {
        $time = new DateTime();
        $time->sub($interval);

        $this->filter_datetime_column_value($this->object_column('created_at'), $time, '>');
        return $this;
    }

    /**
     * @param string    $ipAddress
     * @param int       $interval
     *
     * @return int
     */
    public function get_comments_count_for_ip($ipAddress, $interval = 30)
    {
        $interval = new DateInterval('PT'.(int) $interval.'S');

        return $this
            ->filter_last_records($interval)
            ->filter_ip_address($ipAddress)
            ->count_all();
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function filter_ip_address($value)
    {
        $this->where($this->object_column('ip_address'), '=', (string) $value);
        return $this;
    }

    /**
     * @return bool
     */
    public function is_pending()
    {
        return $this->get_status_id() == Model_ContentCommentStatus::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function is_approved()
    {
        return $this->get_status_id() == Model_ContentCommentStatus::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function is_spam()
    {
        return $this->get_status_id() == Model_ContentCommentStatus::STATUS_SPAM;
    }

    /**
     * @return bool
     */
    public function is_deleted()
    {
        return $this->get_status_id() == Model_ContentCommentStatus::STATUS_DELETED;
    }

    public function init_as_pending()
    {
        $status = $this->get_status_by_id(Model_ContentCommentStatus::STATUS_PENDING);
        return $this->init_status($status);
    }

    public function init_as_approved()
    {
        $status = $this->get_status_by_id(Model_ContentCommentStatus::STATUS_APPROVED);
        return $this->init_status($status);
    }

    public function init_as_spam()
    {
        $status = $this->get_status_by_id(Model_ContentCommentStatus::STATUS_SPAM);
        return $this->init_status($status);
    }

    public function init_as_deleted()
    {
        $status = $this->get_status_by_id(Model_ContentCommentStatus::STATUS_DELETED);
        return $this->init_status($status);
    }

    public function filter_pending()
    {
        return $this->filter_status(Model_ContentCommentStatus::STATUS_PENDING);
    }

    public function filter_approved()
    {
        return $this->filter_status(Model_ContentCommentStatus::STATUS_APPROVED);
    }

    public function filter_spam()
    {
        return $this->filter_status(Model_ContentCommentStatus::STATUS_SPAM);
    }

    public function filter_deleted()
    {
        return $this->filter_status(Model_ContentCommentStatus::STATUS_DELETED);
    }

    /**
     * @param \Model_ContentEntity $entity
     * @param int                  $entity_item_id
     *
     * @return Model_ContentComment[]
     */
    public function get_entity_item_comments(Model_ContentEntity $entity, $entity_item_id)
    {
        $model = $this->model_factory();

        $model
            ->filter_entity_id($entity->get_id())
            ->filter_entity_item_id($entity_item_id);

        return $model
            ->filter_approved()
            ->order_by_path()
            ->find_all();
    }

    /**
     * @inheritDoc
     */
    public function create(Validation $validation = NULL)
    {
        // Preset default status
        if (!$this->has_current_status()) {
            $this->init_as_pending();
        }

        $path_changed = $this->changed('path');

        /** @var Model_ContentComment $obj */
        $obj = parent::create($validation);

        if (!$path_changed) {
            $obj->set_path()->save();
        }

        return $obj;
    }
}
