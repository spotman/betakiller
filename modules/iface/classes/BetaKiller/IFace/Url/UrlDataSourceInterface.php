<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;

interface UrlDataSourceInterface
{
    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string                                                     $key
     * @param string                                                     $value
     * @param \BetaKiller\IFace\Url\UrlParametersInterface               $parameters
     * @param \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface $resource
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
     */
    public function findByUrlKey($key, $value, UrlParametersInterface $parameters, EntityRelatedAclResourceInterface $resource);

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string                                       $key
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $parameters
     * @param null                                         $limit
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getAvailableItemsByUrlKey($key, UrlParametersInterface $parameters, $limit = null);
}
