<?php

abstract class Model_ContentEntityRelated extends Model_ContentEntity
{
    /**
     * Returns model name which describes files (images or attachments)
     *
     * @return string
     */
    abstract protected function get_file_model_name();

    /**
     * Returns relation key for files model
     *
     * @return string
     */
    abstract protected function get_file_relation_key();

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize()
    {
        $element_key = $this->get_file_relation_key();

        $this->has_many([
            $element_key        =>  [
                'model'         =>  $this->get_file_model_name(),
                'foreign_key'   =>  $this->get_file_relation_key(),
            ],
        ]);

        parent::_initialize();
    }

    protected function get_files_foreign_key()
    {
        return 'entity_id';
    }

    /**
     * @return Model_ContentElementInterface
     * @throws Kohana_Exception
     */
    protected function get_files_relation()
    {
        return $this->get($this->get_file_relation_key());
    }

    /**
     * @return Model_ContentElementInterface
     * @throws Kohana_Exception
     */
    protected function content_file_factory()
    {
        $name = $this->get_file_model_name();
        $model = ORM::factory($name);

        $base = Model_ContentElementInterface::class;

        if (!($model instanceof $base))
            throw new Kohana_Exception('Content file model must extend :base', [':base' => $base]);

        return $model;
    }

    /**
     * @param int $entity_item_id
     * @param Model_User $user
     * @param bool $save_in_db
     * @return Model_ContentElementInterface
     */
    public function create_file($entity_item_id, Model_User $user, $save_in_db = TRUE)
    {
        $model = $this->content_file_factory()
            ->set_entity($this)
            ->set_entity_item_id($entity_item_id)
            ->set_uploaded_by($user);

        if ($save_in_db)
        {
            $model->save()->reload();
        }

        return $model;
    }

    /**
     * Возвращает список элементов, прикреплённых к текущей сущности
     * Опционально можно отфильтровать по ID записи из таблицы сущности
     *
     * @param int|null $item_id
     * @return Database_Result|Model_ContentElementInterface[]
     * @throws Kohana_Exception
     */
    public function get_files($item_id = NULL)
    {
        return $this->get_files_query($item_id)->find_all();
    }

    /**
     * @param array $items_ids
     * @return Model_ContentElementInterface[]
     * @throws Kohana_Exception
     */
    public function get_files_for_items_ids(array $items_ids)
    {
        $files = $this->get_files_relation();

        $files->filter_entity_item_ids($items_ids);

        return $files
            ->group_by_entity_item_id()
            ->find_all()
            ->as_array('entity_item_id');
    }

    /**
     * @param int|null $item_id
     * @return Model_ContentElementInterface
     */
    protected function get_files_query($item_id = NULL)
    {
        $orm = $this->get_files_relation();

        if ($item_id !== NULL)
        {
            $orm->filter_entity_item_id($item_id);
        }

        return $orm;
    }
}
