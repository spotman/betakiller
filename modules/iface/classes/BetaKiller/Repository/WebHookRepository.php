<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\ElementFilter\AggregateUrlElementFilter;
use BetaKiller\Url\ElementFilter\WebHookUrlElementFilter;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Url\WebHookModelInterface;

class WebHookRepository extends AbstractPredefinedRepository implements DispatchableRepositoryInterface
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @param string $id
     *
     * @return mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findById(string $id)
    {
        throw new RepositoryException('WebHooks have no ID, but codename only');
    }

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface[]|\Traversable
     */
    public function getAll()
    {
        return $this->getRecursiveIterator();
    }

    /**
     * Performs search for model item where the url key property is equal to $value
     *
     * @param string                                          $value
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface
     */
    public function findItemByUrlKeyValue(string $value, UrlContainerInterface $params): UrlParameterInterface
    {
        $webHook = $this->tree->getByCodename($value);

        if (!$webHook instanceof WebHookModelInterface) {
            throw new RepositoryException('UrlElement with codename :codename must be instance of :class', [
                ':codename' => $value,
                ':class'    => WebHookModelInterface::class,
            ]);
        }

        return $webHook;
    }

    /**
     * Returns list of available items (model records) by url key property
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $parameters
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     */
    public function getItemsHavingUrlKey(UrlContainerInterface $parameters): array
    {
        $items = [];

        foreach ($this->getRecursiveIterator() as $item) {
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return WebHookModelInterface::URL_KEY;
    }

    private function getRecursiveIterator(): \RecursiveIteratorIterator
    {
        $filter = new AggregateUrlElementFilter(
            new WebHookUrlElementFilter
        );

        return $this->tree->getRecursiveIteratorIterator(null, $filter);
    }
}
