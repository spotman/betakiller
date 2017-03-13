<?php

use BetaKiller\Content\LinkedContentModelInterface;

class Model_ContentEntity extends ORM
{
    const POSTS_ENTITY_ID = 1;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'content_entities';

        parent::_initialize();
    }

    /**
     * Возвращает символическое имя сущности
     *
     * @return string
     * @throws Kohana_Exception
     */
    public function get_slug()
    {
        return $this->get('slug');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_slug($value)
    {
        return $this->set('slug', $value);
    }

    /**
     * Возвращает имя модели, с которой связана текущая entity
     *
     * @return string
     * @throws Kohana_Exception
     */
    public function get_linked_model_name()
    {
        return $this->get('model_name');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function set_related_model_name($value)
    {
        return $this->set('model_name', $value);
    }

    /**
     * @param string $slug
     * @return Model_ContentEntity
     * @throws Kohana_Exception
     */
    public function find_by_slug($slug)
    {
        $model = $this->where('slug', '=', $slug)->find();

        if (!$model->loaded()) {
            throw new Kohana_Exception('Unknown content entity slug :value', [':value' => $slug]);
        }

        return $model;
    }

    public function get_title()
    {
        return __('content.entities.'.$this->get_slug());
    }

    /**
     * Возвращает инстанс связанной модели
     *
     * @param int|null $id
     *
     * @return LinkedContentModelInterface
     * @throws Exception
     * @throws Kohana_Exception
     */
    public function get_linked_model_instance($id = null)
    {
        $name = $this->get_linked_model_name();
        $model = $this->model_factory($id, $name);
        $target_class = LinkedContentModelInterface::class;

        if (!($model instanceof $target_class)) {
            throw new Kohana_Exception('Entity-linked content model must be an instance of :target, :current given', [
                ':target'   =>  $target_class,
                ':current'  =>  get_class($model),
            ]);
        }

        return $model;
    }
//
//    public function get_related_model_item_title($item_id)
//    {
//        return $this->get_linked_model_instance()->get_title_by_item_id($item_id);
//    }
}
