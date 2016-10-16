<?php

class Model_ContentEntity extends ORM
{
    const ARTICLES_ENTITY_ID = 1;

    protected $_table_name = 'content_entities';

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
     * Возвращает имя модели, с которой связана текущая entity
     *
     * @return string
     * @throws Kohana_Exception
     */
    public function get_related_model_name()
    {
        return $this->get('model_name');
    }

    /**
     * @param string $slug
     * @return Model_ContentEntity
     * @throws Kohana_Exception
     */
    public function find_by_slug($slug)
    {
        $model = $this->where('slug', '=', $slug)->find();

        if (!$model->loaded())
            throw new Kohana_Exception('Unknown admin content entity slug :value', [':value' => $slug]);

        return $model;
    }

    public function get_title()
    {
        return __('content.entities.'.$this->get_slug());
    }

    /**
     * Возвращает инстанс связанной модели
     * 
     * @return HasContentElementsInText
     * @throws Exception
     * @throws Kohana_Exception
     */
    public function get_related_model_instance()
    {
        $name = $this->get_related_model_name();

        $model = Model::factory($name);
        $target_class = HasContentElements::class;

        if (!($model instanceof $target_class))
            throw new Kohana_Exception('Entity-related content model must be instance of :target, :current given', [
                ':target'   =>  $target_class,
                ':current'  =>  get_class($model),
            ]);

        return $model;
    }

    public function get_related_model_item_title($item_id)
    {
        return $this->get_related_model_instance()->get_title_by_item_id($item_id);
    }
}
