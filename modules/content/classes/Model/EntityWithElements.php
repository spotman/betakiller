<?php

use BetaKiller\Content\ContentElementInterface;
use BetaKiller\Model\Entity;
use BetaKiller\Model\UserInterface;

abstract class Model_EntityWithElements extends Entity
{
    /**
     * Returns model name which describes elements (images, attachments, etc)
     *
     * @return string
     */
    abstract protected function get_element_model_name();

    /**
     * Returns relation key for elements model
     *
     * @return string
     */
    abstract protected function get_element_relation_key();

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize()
    {
        $element_key = $this->get_element_relation_key();

        $this->has_many([
            $element_key        =>  [
                'model'         =>  $this->get_element_model_name(),
                'foreign_key'   =>  $this->get_element_entity_foreign_key(),
            ],
        ]);

        parent::_initialize();
    }

    protected function get_element_entity_foreign_key()
    {
        return 'entity_id';
    }

    /**
     * @return ContentElementInterface
     * @throws Kohana_Exception
     */
    protected function get_elements_relation()
    {
        return $this->get($this->get_element_relation_key());
    }

    /**
     * @return ContentElementInterface
     * @throws Kohana_Exception
     */
    protected function content_element_factory()
    {
        $name = $this->get_element_model_name();
        $model = $this->model_factory(null, $name);

        $base = ContentElementInterface::class;

        if (!($model instanceof $base))
            throw new Kohana_Exception('Content file model must extend :base', [':base' => $base]);

        return $model;
    }

    /**
     * @param int $entity_item_id
     * @param UserInterface $user
     * @param bool $save_in_db
     *
     * @return ContentElementInterface
     */
    public function create_file($entity_item_id, UserInterface $user, $save_in_db = TRUE)
    {
        $model = $this->content_element_factory()
            ->set_uploaded_by($user)
            ->set_entity($this)
            ->set_entity_item_id($entity_item_id);

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
     *
     * @return ContentElementInterface[]
     * @throws Kohana_Exception
     */
    public function get_elements($item_id = NULL)
    {
        return $this->get_elements_query($item_id)->get_all();
    }

    /**
     * @param int[] $items_ids
     *
     * @return ContentElementInterface[]
     * @throws Kohana_Exception
     */
    public function get_elements_for_items_ids(array $items_ids)
    {
        $files = $this->get_elements_relation();

        $files->filter_entity_item_ids($items_ids);

        return $files
            ->group_by_entity_item_id()
            ->find_all()
            ->as_array('entity_item_id');
    }

    /**
     * @param int|null $item_id
     *
     * @return ContentElementInterface
     */
    protected function get_elements_query($item_id = NULL)
    {
        $orm = $this->get_elements_relation();

        if ($item_id !== NULL) {
            $orm->filter_entity_item_id($item_id);
        }

        return $orm;
    }
}
