<?php
namespace BetaKiller\Search;

use BetaKiller\Filter\Model\ApplicableFilterModelInterface;

interface ApplicableSearchModelInterface extends ApplicableFilterModelInterface
{
    /**
     * @param $page int
     * @param $itemsPerPage int|null
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    public function getSearchResults(int $page, ?int $itemsPerPage = null): SearchResultsInterface;
}
