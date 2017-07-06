<?php
namespace BetaKiller\Model;

use BetaKiller\Exception;
use ORM;

class Entity extends ORM
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \BetaKiller\Exception
     * @return void
     */
    protected function _initialize(): void
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
    public function getSlug(): string
    {
        return (string)$this->get('slug');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\Entity
     */
    public function setSlug(string $value): Entity
    {
        return $this->set('slug', $value);
    }

    /**
     * Возвращает имя модели, с которой связана текущая entity
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function getLinkedModelName(): string
    {
        return (string)$this->get('model_name');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\Entity
     */
    public function setLinkedModelName(string $value): Entity
    {
        return $this->set('model_name', $value);
    }


    public function getLabel(): string
    {
        return __('entities.'.$this->getSlug());
    }

    /**
     * Returns instance of linked entity
     *
     * @param int $id
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface
     * @throws \BetaKiller\Exception
     */
    public function getLinkedEntityInstance($id): DispatchableEntityInterface
    {
        // TODO Rewrite to EntityManager or something similar
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
}
