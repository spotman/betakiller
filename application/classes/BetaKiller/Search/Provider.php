<?php
namespace BetaKiller\Search;

use BetaKiller\Model\User;

abstract class Provider
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
     * @todo DI
     * @deprecated
     */
    public function setUser(User $user = NULL)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * @return User
     * @todo DI
     * @deprecated
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param $page
     * @param $itemsPerPage
     * @return \BetaKiller\Search\Model\Results
     */
    abstract public function getResults($page, $itemsPerPage);

    /**
     * Helper for getting empty result
     *
     * @return \BetaKiller\Search\Results
     */
    protected function getEmptyResults()
    {
        return Results::factory(0, 0, false);
    }
}
