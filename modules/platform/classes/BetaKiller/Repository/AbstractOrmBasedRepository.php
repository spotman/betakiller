<?php
namespace BetaKiller\Repository;

use BetaKiller\Factory\OrmFactory;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

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
     * @param string $id
     *
     * @return ExtendedOrmInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findById(string $id)
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
    public function getAll(): array
    {
        try {
            return $this->getOrmInstance()->find_all()->as_array();
        } catch (\Kohana_Exception $e) {
            throw RepositoryException::wrap($e);
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
     * @throws \ORM_Validation_Exception
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function save($entity): void
    {
        $this->checkEntityInheritance($entity);

        try {
            $entity->save();
        } catch (\ORM_Validation_Exception $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw RepositoryException::wrap($e);
        }

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
            throw RepositoryException::wrap($e);
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

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return ExtendedOrmInterface|mixed|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function findOne(OrmInterface $orm)
    {
        try {
            $model = $orm->find();
        } catch (\Kohana_Exception $e) {
            throw RepositoryException::wrap($e);
        }

        if (!$model->loaded()) {
            return null;
        }

        return $model;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return array
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function findAll(OrmInterface $orm): array
    {
        try {
            return $orm->find_all()->as_array();
        } catch (\Kohana_Exception $e) {
            throw RepositoryException::wrap($e);
        }
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int                                       $count
     *
     * @return $this|self
     */
    protected function limit(OrmInterface $orm, int $count): self
    {
        $orm->limit($count);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $relationName
     * @param                                           $relatedModel
     *
     * @return \BetaKiller\Repository\AbstractOrmBasedRepository
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function filterRelated(OrmInterface $orm, string $relationName, $relatedModel): self
    {
        if (!$relatedModel instanceof OrmInterface) {
            throw new RepositoryException('Related model :name must implement :must', [
                ':name' => \get_class($relatedModel),
                ':must' => OrmInterface::class,
            ]);
        }

        $orm->filter_related($relationName, $relatedModel);

        return $this;
    }
}
