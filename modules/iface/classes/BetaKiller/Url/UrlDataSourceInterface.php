<?php
namespace BetaKiller\Url;

use BetaKiller\Repository\RepositoryInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;

interface UrlDataSourceInterface extends RepositoryInterface
{
    /**
     * Performs search for model item where the url key property is equal to $value
     *
     * @param string                                          $value
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface
     */
    public function findItemByUrlKeyValue(string $value, UrlContainerInterface $params): UrlParameterInterface;

    /**
     * Returns list of available items (model records) by url key property
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $parameters
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     */
    public function getItemsHavingUrlKey(UrlContainerInterface $parameters): array;

    /**
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     */
    public function getAllAvailableItems(): array;

    /**
     * @return string
     */
    public function getUrlKeyName(): string;
}
