<?php
declare(strict_types=1);

namespace BetaKiller\Url\ElementFilter;

use BetaKiller\Url\UrlElementInterface;

/**
 * Class AggregateUrlElementFilter
 * Aggregation facade for URL element filters
 *
 * @package BetaKiller\Url
 */
class AggregateUrlElementFilter implements AggregateUrlElementFilterInterface
{
    /**
     * Array of filters
     *
     * @var \BetaKiller\Url\ElementFilter\UrlElementFilterInterface[]
     */
    private $filters = [];

    /**
     * AggregateUrlElementFilter constructor.
     *
     * @param UrlElementFilterInterface[]|null $filters
     */
    public function __construct(array $filters = null)
    {
        if ($filters) {
            foreach ($filters as $filter) {
                $this->addFilter($filter);
            }
        }
    }

    /**
     * Adding a filter
     *
     * @param \BetaKiller\Url\ElementFilter\UrlElementFilterInterface $urlFilter
     *
     * @return $this
     */
    public function addFilter(UrlElementFilterInterface $urlFilter): AggregateUrlElementFilterInterface
    {
        $this->filters[] = $urlFilter;

        return $this;
    }

    /**
     * Checking availability IFace URL element by all filters
     *
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return bool
     */
    public function isAvailable(UrlElementInterface $urlElement): bool
    {
        // No filters => available
        if (!$this->filters) {
            return true;
        }

        foreach ($this->filters as $filter) {
            if (!$filter->isAvailable($urlElement)) {
                return false;
            }
        }

        return true;
    }
}
