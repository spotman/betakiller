<?php

namespace BetaKiller\Url;

/**
 * Collector of IFace URL element filters
 */
class UrlElementFilters implements UrlElementFiltersInterface
{
    /**
     * Array of filters
     *
     * @var \BetaKiller\Url\UrlElementFilterInterface[]
     */
    private $filters;


    /**
     * Adding a filter
     *
     * @param \BetaKiller\Url\UrlElementFilterInterface $urlFilter
     *
     * @return $this
     */
    public function addFilter(UrlElementFilterInterface $urlFilter): UrlElementFiltersInterface
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
