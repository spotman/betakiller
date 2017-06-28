<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Model\DispatchableEntityInterface;

interface UrlDataSourceInterface
{
    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string                                       $key
     * @param string                                       $value
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $params
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
     */
    public function findEntityByUrlKey(
        string $key,
        string $value,
        UrlParametersInterface $params
    ): ?DispatchableEntityInterface;

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string                                       $key
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $parameters
     * @param int|null                                     $limit
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getEntitiesByUrlKey(
        string $key,
        UrlParametersInterface $parameters,
        ?int $limit = null
    ): array;
}
