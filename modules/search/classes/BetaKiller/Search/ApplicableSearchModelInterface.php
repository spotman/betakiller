<?php
namespace BetaKiller\Search;

use BetaKiller\Filter\Model\ApplicableFilterModelInterface;

/**
 * Interface ApplicableSearchModelInterface
 *
 * @package    BetaKiller\Search
 * @deprecated Move to repository instead
 */
interface ApplicableSearchModelInterface extends ApplicableFilterModelInterface
{
    /**
     * @param $page         int
     * @param $itemsPerPage int|null
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     * @deprecated Move to repository instead
     */
    public function getSearchResults(int $page, int $itemsPerPage): SearchResultsInterface;
}
