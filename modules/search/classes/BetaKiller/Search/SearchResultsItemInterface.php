<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 09.11.15
 * Time: 18:08
 */

namespace BetaKiller\Search;


interface SearchResultsItemInterface
{
    /**
     * @return array
     */
    public function getSearchResultsItemData(): array;
}
