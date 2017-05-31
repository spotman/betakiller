<?php
namespace BetaKiller\Model;

use BetaKiller\Exception;
use ORM;

class Entity extends ORM
{
    /**
     * TODO remove and replace by helper method for searching by name
     *
     * @deprecated
     */
    const POSTS_ENTITY_ID = 1;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \BetaKiller\Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'entities';

        parent::_initialize();
    }

    /**
     * Returns entity short name (may be used for url creating)
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function getSlug()
    {
        return $this->get('slug');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setSlug($value)
    {
        return $this->set('slug', $value);
    }

    /**
     * Возвращает имя модели, с которой связана текущая entity
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function getLinkedModelName()
    {
        return $this->get('model_name');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLinkedModelName($value)
    {
        return $this->set('model_name', $value);
    }

    /**
     * @param string $slug
     *
     * @return Entity
     * @throws \BetaKiller\Exception
     */
    public function findBySlug($slug)
    {
        $model = $this->where('slug', '=', $slug)->find();

        if (!$model->loaded()) {
            throw new Exception('Unknown entity slug :value', [':value' => $slug]);
        }

        return $model;
    }

    public function getLabel()
    {
        return __('entities.'.$this->getSlug());
    }

    /**
     * Возвращает инстанс связанной модели
     *
     * @param int|null $id
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface
     * @throws \BetaKiller\Exception
     */
    public function getLinkedEntityInstance($id = null)
    {
        $name        = $this->getLinkedModelName();
        $model       = $this->model_factory($id, $name);
        $targetClass = DispatchableEntityInterface::class;

        if (!($model instanceof $targetClass)) {
            throw new Exception('Entity model must be an instance of :target, :current given', [
                ':target'  => $targetClass,
                ':current' => get_class($model),
            ]);
        }

        return $model;
    }
//
//    public function get_related_model_item_title($item_id)
//    {
//        return $this->getLinkedEntityInstance()->get_title_by_item_id($item_id);
//    }
}
