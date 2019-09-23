<?php
namespace BetaKiller\Search\Provider;

use BetaKiller\Model\User;
use BetaKiller\Search\SearchResults;
use BetaKiller\Search\SearchResultsInterface;

abstract class AbstractProvider
{
    /**
     * @var \BetaKiller\Model\User
     * @deprecated
     * @todo DI
     */
    protected $_user;

    /**
     * Add parameters and configure concrete provider
     */
    abstract public function init();

    /**
     * @param \BetaKiller\Model\User|NULL $user
     *
     * @return $this
     * @deprecated Use DI instead
     */
    public function setUser(User $user = null)
    {
        $this->_user = $user;

        return $this;
    }

    /**
     * @return User
     * @deprecated Use DI instead
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param int $page
     * @param int $itemsPerPage
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    abstract public function getResults(int $page, int $itemsPerPage): SearchResultsInterface;

    /**
     * Helper for getting empty result
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    protected function getEmptyResults(): SearchResultsInterface
    {
        return SearchResults::factory([], 0, 0, false);
    }
}
