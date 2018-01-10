<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Entity;

/**
 * Class EntityRepository
 *
 * @package BetaKiller\Repository
 * @method Entity findById(int $id)
 * @method Entity create()
 * @method Entity[] getAll()
 */
class EntityRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $slug
     *
     * @return Entity
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findBySlug(string $slug): Entity
    {
        $orm = $this->getOrmInstance();

        /** @var Entity $model */
        $model = $orm->where('slug', '=', $slug)->find();

        if (!$model->loaded()) {
            throw new RepositoryException('Unknown entity slug :value', [':value' => $slug]);
        }

        return $model;
    }

    /**
     * @param string $modelName
     *
     * @return \BetaKiller\Model\Entity|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByModelName(string $modelName): Entity
    {
        $orm = $this->getOrmInstance();

        $model = $orm->where('model_name', '=', $modelName)->find();

        if (!$model->loaded()) {
            throw new RepositoryException('Unknown entity model name :value', [':value' => $modelName]);
        }

        return $model;
    }
}
