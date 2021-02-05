<?php
namespace BetaKiller\Factory;

use BetaKiller\Repository\RepositoryInterface;

interface RepositoryFactoryInterface
{
    /**
     * /**
     * @param string $codename
     *
     * @return \BetaKiller\Repository\RepositoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename): RepositoryInterface;
}
