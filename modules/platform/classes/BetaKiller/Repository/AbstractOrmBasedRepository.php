<?php
namespace BetaKiller\Repository;

use BetaKiller\Factory\OrmFactory;
use BetaKiller\Helper\ExceptionTranslator;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Search\SearchResultsInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use ORM\PaginateHelper;

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
     * @return AbstractEntityInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findById(string $id)
    {
        try {
            return $this->getOrmInstance()->get_by_id($id, true);
        } catch (\Kohana_Exception $e) {
            throw RepositoryException::wrap($e);
        }
    }

    /**
     * @param string $id
     *
     * @return AbstractEntityInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByID(string $id)
    {
        $model = $this->findById($id);

        if (!$model) {
            throw new RepositoryException('Can not find item in [:repo] repo by id = :id', [
                ':repo' => static::getCodename(),
                ':id'   => $id,
            ]);
        }

        return $model;
    }

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface[]
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAll(): array
    {
        $orm = $this->getOrmInstance();

        // Add ordering
        $this->customOrderBy($orm);

        return $this->findAll($orm);
    }

    /**
     * @param int|null $currentPage
     * @param int|null $itemsPerPage
     *
     * @return \BetaKiller\Model\AbstractEntityInterface[]
     */
    public function getAllPaginated(int $currentPage, int $itemsPerPage): array
    {
        $orm = $this->getOrmInstance();

        // Add ordering
        $this->customOrderBy($orm);

        return $this->findAll($orm, $currentPage, $itemsPerPage);
    }

    /**
     * @param ExtendedOrmInterface|mixed $entity
     *
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function save($entity): void
    {
        $this->checkEntityInheritance($entity);

        try {
            $entity->save();
        } catch (\ORM_Validation_Exception $e) {
            throw ExceptionTranslator::fromOrmValidationException($e);
        } catch (\Throwable $e) {
            throw RepositoryException::wrap($e);
        }

        // Force updating all related models with IDs
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

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function checkEntityInheritance(AbstractEntityInterface $entity): void
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
            $model = $orm->limit(1)->find();
        } catch (\Kohana_Exception $e) {
            throw RepositoryException::wrap($e);
        }

        if (!$model->loaded()) {
            return null;
        }

        return $model;
    }

    protected function countAll(OrmInterface $orm): int
    {
        return $orm->count_all();
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Model\ExtendedOrmInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function getOne(OrmInterface $orm)
    {
        $model = $this->findOne($orm);

        if (!$model) {
            throw new RepositoryException('Can not find item in [:repo] repo with query ":query"', [
                ':repo'  => static::getCodename(),
                ':query' => $orm->last_query(),
            ]);
        }

        return $model;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @param int|null                               $currentPage
     * @param int|null                               $itemsPerPage
     *
     * @return ExtendedOrmInterface[]|mixed[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function findAll(ExtendedOrmInterface $orm, int $currentPage = null, int $itemsPerPage = null): array
    {
        try {
            // Add ordering
            $this->customOrderBy($orm);

            if (!$currentPage || !$itemsPerPage) {
                // Raw data
                return $orm->find_all()->as_array();
            }

            // Wrap in a pager
            $pager = PaginateHelper::create(
                $orm,
                $currentPage,
                $itemsPerPage
            );

            return $pager->getResults();
        } catch (\Kohana_Exception $e) {
            throw RepositoryException::wrap($e);
        }
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param int                                    $currentPage
     * @param int                                    $itemsPerPage
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     * @throws \BetaKiller\Exception
     */
    protected function findAllResults(
        ExtendedOrmInterface $orm,
        int $currentPage,
        int $itemsPerPage
    ): SearchResultsInterface {
        try {
            // Wrap in a pager
            $pager = PaginateHelper::create(
                $orm,
                $currentPage,
                $itemsPerPage
            );

            return $pager->getSearchResults();
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
     * @param \BetaKiller\Model\AbstractEntityInterface $relatedModel
     *
     * @return \BetaKiller\Repository\AbstractOrmBasedRepository
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function filterRelated(
        OrmInterface $orm,
        string $relationName,
        AbstractEntityInterface $relatedModel
    ): self {
        if (!$relatedModel instanceof OrmInterface) {
            throw new RepositoryException('Related model :name must implement :must', [
                ':name' => \get_class($relatedModel),
                ':must' => OrmInterface::class,
            ]);
        }

        $orm->filter_related($relationName, $relatedModel);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface   $orm
     * @param string                                      $relationName
     * @param \BetaKiller\Model\AbstractEntityInterface[] $relatedModels
     *
     * @return \BetaKiller\Repository\AbstractOrmBasedRepository
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function filterRelatedMultiple(
        OrmInterface $orm,
        string $relationName,
        array $relatedModels
    ): self {
        foreach ($relatedModels as $model) {
            if (!$model instanceof OrmInterface) {
                throw new RepositoryException('Related model :name must implement :must', [
                    ':name' => \get_class($model),
                    ':must' => OrmInterface::class,
                ]);
            }
        }

        $orm->filter_related_multiple($relationName, $relatedModels);

        return $this;
    }

    protected function customOrderBy(OrmInterface $orm): void
    {
        // Empty by default
    }
}
