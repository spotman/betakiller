<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Entity;

class EntityRepository extends AbstractOrmBasedRepository
{
    /**
     * Creates empty entity
     *
     * @return mixed
     */
    public function create(): Entity
    {
        return parent::create();
    }

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
     * @return \BetaKiller\Model\Entity[]
     */
    public function getAllEntities(): array
    {
        return $this->getOrmInstance()->get_all();
    }
}
