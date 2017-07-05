<?php
namespace BetaKiller\Search;

use BetaKiller\Filter;

interface ApplicableModelInterface extends Filter\Model\ApplicableInterface
{
    /**
     * @param $page int
     * @param $itemsPerPage int|null
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    public function getSearchResults(int $page, ?int $itemsPerPage = null): SearchResultsInterface;
}
