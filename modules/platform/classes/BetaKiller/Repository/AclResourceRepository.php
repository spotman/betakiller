<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\AclResource;
use BetaKiller\Model\ExtendedOrmInterface;

/**
 * Class UserRepository
 *
 * @package BetaKiller\Repository
 * @method AclResource create()
 * @method AclResource findById(int $id)
 * @method AclResource[] getAll()
 * @method AclResource getOrmInstance()
 */
class AclResourceRepository extends AbstractOrmBasedSingleParentTreeRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return AclResource::URL_KEY;
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\AclResource
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByName(string $name): AclResource
    {
        $orm = $this->getOrmInstance();

        $this->filterName($orm, $name);

        return $this->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $name
     *
     * @return \BetaKiller\Repository\AclResourceRepository
     */
    private function filterName(ExtendedOrmInterface $orm, string $name): self
    {
        $orm->where($orm->object_column('name'), '=', $name);

        return $this;
    }

    /**
     * @return string
     */
    protected function getParentIdColumnName(): string
    {
        return 'parent_id';
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return void
     */
    protected function customFilterForTreeTraversing(ExtendedOrmInterface $orm): void
    {
        // Nothing to do here
    }
}
