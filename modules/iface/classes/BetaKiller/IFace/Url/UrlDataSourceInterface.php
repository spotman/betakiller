<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\Model\DispatchableEntityInterface;

interface UrlDataSourceInterface
{
    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string                                                     $key
     * @param string                                                     $value
     * @param \BetaKiller\IFace\Url\UrlParametersInterface               $params
     * @param \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface $resource
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
     */
    public function findByUrlKey(
        string $key,
        string $value,
        UrlParametersInterface $params,
        EntityRelatedAclResourceInterface $resource
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
    public function getAvailableItemsByUrlKey(
        string $key,
        UrlParametersInterface $parameters,
        ?int $limit = null
    ): array;
}
