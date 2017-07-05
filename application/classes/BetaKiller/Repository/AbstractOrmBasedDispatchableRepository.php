<?php
namespace BetaKiller\Repository;

use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\IFace\Url\UrlDataSourceInterface;
use BetaKiller\IFace\Url\UrlParameterInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

abstract class AbstractOrmBasedDispatchableRepository extends AbstractOrmBasedRepository implements UrlDataSourceInterface
{
    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string                                      $key
     * @param string                                      $value
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $parameters
     *
     * @return UrlParameterInterface|null
     */
    public function findItemByUrlKeyValue(
        string $key,
        string $value,
        UrlContainerInterface $parameters
    ): ?UrlParameterInterface
    {
        $orm = $this->getOrmInstance();

        // Additional filtering for non-pk keys
        if ($key !== $orm->primary_key()) {
            $this->customFilterForUrlDispatching($orm, $parameters);
        }

        $model = $orm->where($orm->object_column($key), '=', $value)->find();

        return $model->loaded() ? $model : null;
    }

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string                $key
     * @param UrlContainerInterface $parameters
     * @param int|null              $limit
     *
     * @return \BetaKiller\IFace\Url\UrlParameterInterface[]
     */
    public function getItemsByUrlKey(
        string $key,
        UrlContainerInterface $parameters,
        ?int $limit = null
    ): array {
        $orm = $this->getOrmInstance();

        // Additional filtering for non-pk keys
        if ($key !== $orm->primary_key()) {
            $this->customFilterForUrlDispatching($orm, $parameters);
        }

        if ($limit) {
            $orm->limit($limit);
        }

        $key_column = $orm->object_column($key);

        $result = $orm->where($key_column, 'IS NOT', null)->group_by($key_column)->find_all();

        return $result->count() ? $result->as_array() : [];
    }

    protected function customFilterForUrlDispatching(OrmInterface $orm, UrlContainerInterface $params): void
    {
        // Empty by default
    }
}
