<?php
namespace BetaKiller\IFace\Url;

interface UrlDataSourceInterface
{
    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string                                       $key
     * @param string                                       $value
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $parameters
     *
     * @return \BetaKiller\IFace\Url\DispatchableEntityInterface|null
     */
    public function findByUrlKey($key, $value, UrlParametersInterface $parameters);

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string                                       $key
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $parameters
     * @param null                                         $limit
     *
     * @return \BetaKiller\IFace\Url\DispatchableEntityInterface[]
     */
    public function getAvailableItemsByUrlKey($key, UrlParametersInterface $parameters, $limit = null);
}
