<?php

use BetaKiller\Content\ContentRelatedInterface;

class Model_ContentComment extends ORM implements ContentRelatedInterface
{
    use Model_ORM_ContentRelatedTrait;

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
        ]);

        parent::_initialize();
    }

    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ip_address'   =>  [
                ['not_empty'],
            ],
            'name'   =>  [
                ['not_empty'],
            ],
            'email'   =>  [
                ['not_empty'],
                ['email'],
            ],
            'message'   =>  [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_email($value)
    {
        $this->set('email', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function get_email()
    {
        return $this->get('email');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_name($value)
    {
        $this->set('name', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->get('name');
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
            ->order_by_created_at()
            ->find_all();
    }

    // TODO approving comments via statuses
}
