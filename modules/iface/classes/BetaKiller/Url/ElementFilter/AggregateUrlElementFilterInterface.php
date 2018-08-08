<?php
namespace BetaKiller\Url\ElementFilter;

use BetaKiller\Url\UrlElementInterface;

/**
 * Collector of IFace URL element filters
 */
interface AggregateUrlElementFilterInterface extends UrlElementFilterInterface
{
    /**
     * Adding a filter
     *
     * @param \BetaKiller\Url\ElementFilter\UrlElementFilterInterface $urlFilter
     *
     * @return $this
     */
    public function addFilter(UrlElementFilterInterface $urlFilter): AggregateUrlElementFilterInterface;

    /**
     * Checking availability IFace URL element by all filters
     *
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return bool
     */
    public function isAvailable(UrlElementInterface $urlElement): bool;
}
