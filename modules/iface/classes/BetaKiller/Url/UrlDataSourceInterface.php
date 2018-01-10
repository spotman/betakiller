<?php
namespace BetaKiller\Url;

use BetaKiller\Repository\RepositoryInterface;

interface UrlDataSourceInterface extends RepositoryInterface
{
    /**
     * Performs search for model item where the url key property is equal to $value
     *
     * @param string                                $value
     * @param \BetaKiller\Url\UrlContainerInterface $params
     *
     * @return \BetaKiller\Url\UrlParameterInterface
     */
    public function findItemByUrlKeyValue(string $value, UrlContainerInterface $params): UrlParameterInterface;

    /**
     * Returns list of available items (model records) by url key property
     *
     * @param \BetaKiller\Url\UrlContainerInterface $parameters
     *
     * @return \BetaKiller\Url\UrlParameterInterface[]
     */
    public function getItemsHavingUrlKey(UrlContainerInterface $parameters): array;

    /**
     * @return string
     */
    public function getUrlKeyName(): string;
}
