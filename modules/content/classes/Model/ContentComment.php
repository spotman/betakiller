<?php

use BetaKiller\Content\ContentRelatedInterface;
use BetaKiller\Content\ImportedFromWordpressInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;
use BetaKiller\Model\UserInterface;

class Model_ContentComment extends TreeModelSingleParentOrm implements ContentRelatedInterface, ImportedFromWordpressInterface
{
    use Model_ORM_ContentRelatedTrait;
    use Model_ORM_ImportedFromWordpressTrait;

    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_SPAM = 3;
    const STATUS_DELETED = 4;

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

        parent::_initialize();
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
            'status'   =>  [
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
     * @return bool
     */
    public function has_status()
    {
        return (bool) $this->get_status();
    }

    /**
     * @return bool
     */
    public function is_pending()
    {
        return $this->get_status() == self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function is_approved()
    {
        return $this->get_status() == self::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function is_spam()
    {
        return $this->get_status() == self::STATUS_SPAM;
    }

    /**
     * @return bool
     */
    public function is_deleted()
    {
        return $this->get_status() == self::STATUS_DELETED;
    }

    public function mark_as_pending()
    {
        return $this->set_status(self::STATUS_PENDING);
    }

    public function mark_as_approved()
    {
        return $this->set_status(self::STATUS_APPROVED);
    }

    public function mark_as_spam()
    {
        return $this->set_status(self::STATUS_SPAM);
    }

    public function mark_as_deleted()
    {
        return $this->set_status(self::STATUS_DELETED);
    }

    /**
     * @param int $id
     */
    protected function set_status($id)
    {
        $this->set('status', (int) $id);
    }

    /**
     * @return int
     */
    protected function get_status()
    {
        return $this->get('status');
    }

    public function filter_pending()
    {
        return $this->filter_status(self::STATUS_PENDING);
    }

    public function filter_approved()
    {
        return $this->filter_status(self::STATUS_APPROVED);
    }

    public function filter_spam()
    {
        return $this->filter_status(self::STATUS_SPAM);
    }

    public function filter_deleted()
    {
        return $this->filter_status(self::STATUS_DELETED);
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    protected function filter_status($value)
    {
        return $this->where($this->object_column('status'), '=', $value);
    }

    /**
     * @param \Model_ContentEntity $entity
     * @param int                  $entity_item_id
     *
     * @return \Database_Result|Model_ContentComment[]
     */
    public function get_entity_item_comments(Model_ContentEntity $entity, $entity_item_id)
    {
        return $this->model_factory()
            ->filter_entity_id($entity->get_id())
            ->filter_entity_item_id($entity_item_id)
            ->filter_approved()
            ->order_by_created_at()
            ->find_all();
    }
}
