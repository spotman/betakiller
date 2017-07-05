<?php
namespace BetaKiller\IFace\Url;

interface UrlDataSourceInterface
{
    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string                                      $key
     * @param string                                      $value
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $params
     *
     * @return \BetaKiller\IFace\Url\UrlParameterInterface|null
     */
    public function findItemByUrlKeyValue(
        string $key,
        string $value,
        UrlContainerInterface $params
    ): ?UrlParameterInterface;

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string                                      $key
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $parameters
     * @param int|null                                    $limit
     *
     * @return \BetaKiller\IFace\Url\UrlParameterInterface[]
     */
    public function getItemsByUrlKey(
        string $key,
        UrlContainerInterface $parameters,
        ?int $limit = null
    ): array;
}
