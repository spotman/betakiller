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
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findById(int $id)
    {
        try {
            return $this->getOrmInstance()->get_by_id($id);
        } catch (\Kohana_Exception $e) {
            throw new RepositoryException('Can not find item in [:repo] repo by id = :id', [
                ':repo' => static::getCodename(),
                ':id'   => $id,
            ], $e->getCode(), $e);
        }
    }

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface[]|\Traversable
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAll()
    {
        try {
            return $this->getOrmInstance()->get_all();
        } catch (\Kohana_Exception $e) {
            throw new RepositoryException(':error', [':error' => $e->getMessage()], $e->getCode(), $e);
        }
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
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \ORM_Validation_Exception
     */
    public function save($entity): void
    {
        $this->checkEntityInheritance($entity);

        $entity->save();

        // Force updating all the related models
        $entity->reload();
    }

    /**
     * @param ExtendedOrmInterface|mixed $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function delete($entity): void
    {
        $this->checkEntityInheritance($entity);

        try {
            $entity->delete();
        } catch (\Kohana_Exception $e) {
            throw new RepositoryException(':error', [':error' => $e->getMessage()], $e->getCode(), $e);
        }
    }

    public function getValidationExceptionErrors(\ORM_Validation_Exception $e): array
    {
        return $e->errors('models');
    }

    /**
     * @param $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function checkEntityInheritance($entity): void
    {
        if (!($entity instanceof ExtendedOrmInterface)) {
            throw new RepositoryException('Entity :class must be instance of :must', [
                ':class' => \get_class($entity),
                ':must'  => ExtendedOrmInterface::class,
            ]);
        }
    }

    /**
     * @return \BetaKiller\Model\ExtendedOrmInterface|mixed
     */
    protected function getOrmInstance()
    {
        $name = static::getCodename();

        return $this->ormFactory->create($name);
    }
}
