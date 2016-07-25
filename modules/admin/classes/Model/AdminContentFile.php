<?php

// TODO refactoring to interface + trait
// Model_AdminImageFile extends Assets_Model_ORM_Image
// Model_AdminAttachmentFile extends Assets_Model_ORM
abstract class Model_AdminContentFile extends Assets_Model_ORM_Image
{
    abstract protected function get_file_table_name();

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = $this->get_file_table_name();

        $this->belongs_to([
            'entity'    =>  [
                'model'         =>  'AdminContentEntity',
                'foreign_key'   =>  'entity_id',
            ],
        ]);

        $this->load_with(['entity']);

        parent::_initialize();
    }

    /**
     * @param Model_AdminContentEntity $entity
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_entity(Model_AdminContentEntity $entity)
    {
        return $this->set('entity', $entity);
    }

    /**
     * @return Model_AdminContentEntity
     * @throws Kohana_Exception
     */
    public function get_entity()
    {
        return $this->get('entity');
    }

    /**
     * @return string
     */
    public function get_entity_slug()
    {
        return $this->get_entity()->get_slug();
    }

    /**
     * Устанавливает ссылку на ID записи из таблицы, к которой привязана entity
     *
     * @param int $id
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_entity_item_id($id)
    {
        return $this->set('entity_item_id', (int) $id);
    }

    /**
     * @return int
     * @throws Kohana_Exception
     */
    public function get_entity_item_id()
    {
        return $this->get('entity_item_id');
    }

    /**
     * @param int $value
     * @return $this|ORM
     */
    public function set_wp_id($value)
    {
        return $this->set('wp_id', (int) $value);
    }

    /**
     * @return int|null
     */
    public function get_wp_id()
    {
        return $this->get('wp_id');
    }

    /**
     * @param string $value
     * @return $this|ORM
     */
    public function set_wp_path($value)
    {
        return $this->set('wp_path', $value);
    }

    /**
     * @return string|null
     */
    public function get_wp_path()
    {
        return $this->get('wp_path');
    }

    /**
     * @return Database_Result|Model_AdminContentFile[]
     * @throws Kohana_Exception
     */
    public function get_all_files()
    {
        if (!$this->current_user(TRUE))
        {
            // Кешируем запрос для всех кроме админов
            $this->cached();
        }

        return $this->find_all();
    }

    /**
     * @param int $id
     * @return null|Model_AdminContentFile
     */
    public function find_by_wp_id($id)
    {
        $model = $this->filter_wp_id($id)->find();

        return $model->loaded() ? $model : NULL;
    }

    public function find_by_wp_path($wp_path)
    {
        $model = $this->filter_wp_path($wp_path)->find();

        return $model->loaded() ? $model : NULL;
    }

    public function get_entity_items_ids(Model_AdminContentEntity $entity)
    {
        return $this->model_factory()
            ->filter_entity_id($entity->get_id())
            ->group_by_entity_item_id()
            ->find_all()
            ->as_array(NULL, 'entity_item_id');
    }

    public function filter_entity_item_id($item_id)
    {
        return $this->where('entity_item_id', '=', $item_id);
    }

    public function filter_entity_item_ids(array $item_ids)
    {
        return $this->where('entity_item_id', 'IN', $item_ids);
    }

    public function filter_entity_id($entity_id)
    {
        return $this->where('entity_id', '=', $entity_id);
    }

    public function filter_wp_id($wp_id)
    {
        return $this->where('wp_id', '=', $wp_id);
    }

    public function filter_wp_path($wp_path)
    {
        return $this->where('wp_path', '=', $wp_path);
    }

    public function group_by_entity_item_id()
    {
        return $this->group_by('entity_item_id');
    }

    /**
     * Returns array representation of the model
     *
     * @return array
     */
    public function to_json()
    {
        return $this->as_array();
    }

    /**
     * Returns array of image IDs by their WP IDs
     *
     * @param int|array $wp_ids
     * @return array
     */
    public function find_ids_by_wp_ids($wp_ids)
    {
        /** @var $this $model */
        $model = $this->model_factory();

        return $model
            ->where($this->object_column('wp_id'), 'IN', (array) $wp_ids)
            ->order_by_wp_ids($wp_ids)
            ->find_all()
            ->as_array(NULL, 'id');
    }

    protected function order_by_wp_ids(array $wp_ids)
    {
        return $this->order_by(DB::expr('FIELD('.$this->object_column('wp_id').', '.implode(', ', $wp_ids).')'), 'ASC');
    }
}
