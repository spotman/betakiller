<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\EntityModelInterface;

/**
 * Class EntityRepository
 *
 * @package BetaKiller\Repository
 * @method EntityModelInterface findById(int $id)
 * @method EntityModelInterface create()
 * @method EntityModelInterface[] getAll()
 */
class EntityRepository extends AbstractOrmBasedDispatchableRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return EntityModelInterface::URL_KEY;
    }

    /**
     * @param string $slug
     *
     * @return EntityModelInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findBySlug(string $slug): EntityModelInterface
    {
        $orm = $this->getOrmInstance();

        $model = $orm->where('slug', '=', $slug)->find();

        if (!$model->loaded()) {
            throw new RepositoryException('Unknown entity slug :value', [':value' => $slug]);
        }

        return $model;
    }

    /**
     * @param string $modelName
     *
     * @return \BetaKiller\Model\EntityModelInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByModelName(string $modelName): EntityModelInterface
    {
        $orm = $this->getOrmInstance();

        $model = $orm->where('model_name', '=', $modelName)->find();

        if (!$model->loaded()) {
            throw new RepositoryException('Unknown entity model name :value', [':value' => $modelName]);
        }

        return $model;
    }
}
