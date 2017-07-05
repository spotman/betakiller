<?php
namespace BetaKiller\Repository;

use BetaKiller\Factory\OrmFactory;
use BetaKiller\Model\ExtendedOrmInterface;

abstract class AbstractOrmBasedRepository extends AbstractRepository
{
    /**
     * @var \BetaKiller\Factory\OrmFactory
     */
    private $ormFactory;

    /**
     * AbstractOrmBasedRepository constructor.
     *
     * @param \BetaKiller\Factory\OrmFactory $ormFactory
     */
    public function __construct(OrmFactory $ormFactory)
    {
        $this->ormFactory = $ormFactory;
    }

    /**
     * @param int $id
     *
     * @return ExtendedOrmInterface|mixed
     */
    public function findById(int $id)
    {
        return $this->getOrmInstance()->get_by_id($id);
    }

    /**
     * Creates empty entity
     *
     * @return ExtendedOrmInterface|mixed
     */
    public function create()
    {
        return $this->getOrmInstance();
    }

    /**
     * @param ExtendedOrmInterface|mixed $entity
     */
    public function save($entity): void
    {
        $this->checkEntityInheritance($entity);

        $entity->save();
    }

    /**
     * @param ExtendedOrmInterface|mixed $entity
     */
    public function delete($entity): void
    {
        $this->checkEntityInheritance($entity);

        $entity->delete();
    }

    private function checkEntityInheritance($entity): void
    {
        if (!($entity instanceof ExtendedOrmInterface)) {
            throw new RepositoryException('Entity :class must be instance of :must', [
                ':class' => get_class($entity),
                ':must'  => ExtendedOrmInterface::class,
            ]);
        }
    }

    protected function getOrmInstance(): ExtendedOrmInterface
    {
        $name = static::getCodename();

        return $this->ormFactory->create($name);
    }
}
