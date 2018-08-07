<?php
namespace BetaKiller\Url;

/**
 * Collector of IFace URL element filters
 */
interface AggregateUrlElementFilterInterface extends UrlElementFilterInterface
{
    /**
     * Adding a filter
     *
     * @param \BetaKiller\Url\UrlElementFilterInterface $urlFilter
     *
     * @return $this
     */
    public function addFilter(UrlElementFilterInterface $urlFilter): self;

    /**
     * Checking availability IFace URL element by all filters
     *
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return bool
     */
    public function isAvailable(UrlElementInterface $urlElement): bool;
}
