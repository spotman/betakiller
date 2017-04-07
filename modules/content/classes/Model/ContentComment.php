<?php

use BetaKiller\Content\ContentRelatedInterface;
use BetaKiller\Content\ImportedFromWordpressInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;
use BetaKiller\Model\UserInterface;
use BetaKiller\Status\StatusRelatedModelOrmTrait;
use BetaKiller\Status\StatusRelatedModelInterface;
use BetaKiller\Helper\HasPublicUrlInterface;
use BetaKiller\Helper\HasAdminUrlInterface;
use BetaKiller\Helper\IFaceTrait;
use Spotman\Api\AbstractCrudMethodsModelInterface;

class Model_ContentComment extends TreeModelSingleParentOrm
    implements ContentRelatedInterface, StatusRelatedModelInterface, ImportedFromWordpressInterface, HasPublicUrlInterface, HasAdminUrlInterface, AbstractCrudMethodsModelInterface
{
    use Model_ORM_ContentRelatedTrait;
    use Model_ORM_ImportedFromWordpressTrait;
    use StatusRelatedModelOrmTrait;
    use IFaceTrait;

    const URL_PARAM = 'ContentComment';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'content_comments';

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

    public function get_public_url()
    {
        return $this->get_related_content_public_url().'#'.$this->get_html_dom_id();
    }

    public function get_related_content_public_url()
    {
        return $this->get_related_item_model()->get_public_url();
    }

    public function get_admin_url()
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentItem $iface */
        $iface = $this->iface_from_codename('Admin_Content_CommentItem');

        $params = $this->url_parameters_instance()->set(self::URL_PARAM, $this);

        return $iface->url($params);
    }

//    public function get_related_content_admin_url()
//    {
//        return $this->get_related_item_model()->get_admin_url();
//    }

    public function get_related_content_label()
    {
        return $this->get_related_item_model()->get_label();
    }

    public function get_html_dom_id()
    {
        return 'content-comment-'.$this->get_id();
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

    /**
     * @return bool
     */
    public function author_is_guest()
    {
        return !$this->get_author_user();
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

    public function set_created_at(DateTime $value = null)
    {
        $this->set_datetime_column_value('created_at', $value ?: new DateTime);
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
        $key = 'PT'.(int) $interval.'S';

        return $this
            ->filter_last_records(new DateInterval($key))
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
        return $this->get_status_id() === Model_ContentCommentStatus::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function is_approved()
    {
        return $this->get_status_id() === Model_ContentCommentStatus::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function is_spam()
    {
        return $this->get_status_id() === Model_ContentCommentStatus::STATUS_SPAM;
    }

    /**
     * @return bool
     */
    public function is_deleted()
    {
        return $this->get_status_id() === Model_ContentCommentStatus::STATUS_TRASH;
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

    public function init_as_trash()
    {
        $status = $this->get_status_by_id(Model_ContentCommentStatus::STATUS_TRASH);
        return $this->init_status($status);
    }

    protected function filter_pending()
    {
        return $this->filter_status_id(Model_ContentCommentStatus::STATUS_PENDING);
    }

    protected function filter_approved()
    {
        return $this->filter_status_id(Model_ContentCommentStatus::STATUS_APPROVED);
    }

    protected function filter_spam()
    {
        return $this->filter_status_id(Model_ContentCommentStatus::STATUS_SPAM);
    }

    protected function filter_trash()
    {
        return $this->filter_status_id(Model_ContentCommentStatus::STATUS_TRASH);
    }

    /**
     * @param \Model_ContentEntity $entity
     * @param int                  $entity_item_id
     *
     * @return Model_ContentComment[]
     */
    public function get_entity_item_approved_comments(Model_ContentEntity $entity, $entity_item_id)
    {
        /** @var Model_ContentCommentStatus $status */
        $status = $this->get_status_relation();
        $status = $status->get_approved_status();

        return $this->get_comments_by_status($status, $entity, $entity_item_id);
    }

    public function get_comments_by_status(Model_ContentCommentStatus $status, Model_ContentEntity $entity = null, $entity_item_id = null)
    {
        $model = $this->model_factory();

        $model
            ->filter_entity_and_entity_item_id($entity, $entity_item_id)
            ->filter_status($status);

        return $model
            ->order_by_path()
            ->get_all();
    }

    public function get_pending_comments_count()
    {
        /** @var \Model_ContentCommentStatus $statusOrm */
        $statusOrm = $this->status_model_factory();
        $status = $statusOrm->get_pending_status();

        return $this->get_comments_count($status);
    }

    public function get_comments_count(Model_ContentCommentStatus $status = null, Model_ContentEntity $entity = null, $entity_item_id = null)
    {
        $model = $this->model_factory();

        $model->filter_entity_and_entity_item_id($entity, $entity_item_id);

        if ($status) {
            $model->filter_status($status);
        }

        return $model->compile_as_subquery_and_count_all();
    }

    public function isApproveAllowed()
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

    public function delete()
    {
        // TODO Delete child comments

        return parent::delete();
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
