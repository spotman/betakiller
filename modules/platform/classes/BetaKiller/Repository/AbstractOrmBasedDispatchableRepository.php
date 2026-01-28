<?php
namespace BetaKiller\Repository;

use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

abstract class AbstractOrmBasedDispatchableRepository extends AbstractOrmBasedRepository implements
    DispatchableRepositoryInterface
{
    /**
     * Performs search for model item where the url key property is equal to $value
     *
     * @param string                                          $value
     * @param \BetaKiller\Url\Container\UrlContainerInterface $parameters
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface|null
     * @throws \Kohana_Exception
     */
    public function findItemByUrlKeyValue(
        string                $value,
        UrlContainerInterface $parameters
    ): ?UrlParameterInterface {
        $orm = $this->getOrmInstance();
        $key = $this->getUrlKeyName();

        // Additional filtering for non-pk keys
        $this->customFilterForUrlDispatching($orm, $parameters);

        $model = $orm->where($orm->object_column($key), '=', $value)->find();

        return $model->loaded() ? $model : null;
    }

    /**
     * Returns list of available items (model records) by url key property
     *
     * @param UrlContainerInterface $parameters
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     * @throws \Kohana_Exception
     */
    public function getItemsHavingUrlKey(UrlContainerInterface $parameters): array
    {
        $orm = $this->getOrmInstance();

        // Additional filtering for non-pk keys
        $this->customFilterForUrlDispatching($orm, $parameters);

        // Add ordering
        $this->applyDefaultOrderBy($orm);

        $keyName   = $this->getUrlKeyName();
        $keyColumn = $orm->object_column($keyName);

        $result = $orm->where($keyColumn, 'IS NOT', null)->group_by($keyColumn)->find_all();

        return $result->count() ? $result->as_array() : [];
    }

    /**
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     */
    public function getAllAvailableItems(): array
    {
        return $this->getAll();
    }

    protected function customFilterForUrlDispatching(OrmInterface $orm, UrlContainerInterface $params): void
    {
        // Empty by default
    }
}
