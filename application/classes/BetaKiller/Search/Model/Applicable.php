<?php
namespace BetaKiller\Search\Model;

use \BetaKiller\Filter;

interface Applicable extends Filter\Model\Applicable
{
    /**
     * @param $page int
     * @param $itemsPerPage int|null
     * @return \BetaKiller\Search\Model\Results
     */
    public function getSearchResults($page, $itemsPerPage = null);
}
