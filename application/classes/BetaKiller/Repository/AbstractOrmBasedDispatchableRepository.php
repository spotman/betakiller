<?php
namespace BetaKiller\Repository;

use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\IFace\Url\UrlDataSourceInterface;
use BetaKiller\IFace\Url\UrlParameterInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

abstract class AbstractOrmBasedDispatchableRepository extends AbstractOrmBasedRepository implements UrlDataSourceInterface
{
    /**
     * Performs search for model item where the url key property is equal to $value
     *
     * @param string                                      $value
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $parameters
     *
     * @return UrlParameterInterface|null
     */
    public function findItemByUrlKeyValue(
        string $value,
        UrlContainerInterface $parameters
    ): ?UrlParameterInterface
    {
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
     * @return \BetaKiller\IFace\Url\UrlParameterInterface[]
     */
    public function getItemsHavingUrlKey(UrlContainerInterface $parameters): array {
        $orm = $this->getOrmInstance();

        // Additional filtering for non-pk keys
        $this->customFilterForUrlDispatching($orm, $parameters);

        $keyColumn = $orm->object_column($this->getUrlKeyName());

        $result = $orm->where($keyColumn, 'IS NOT', null)->group_by($keyColumn)->find_all();

        return $result->count() ? $result->as_array() : [];
    }

    protected function customFilterForUrlDispatching(OrmInterface $orm, UrlContainerInterface $params): void
    {
        // Empty by default
    }
}
