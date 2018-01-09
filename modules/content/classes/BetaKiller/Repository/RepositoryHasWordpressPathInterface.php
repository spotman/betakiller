<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\EntityHasWordpressPathInterface;

interface RepositoryHasWordpressPathInterface
{
    /**
     * @param string $wp_path
     * @return EntityHasWordpressPathInterface|null
     */
    public function findByWpPath($wp_path): ?EntityHasWordpressPathInterface;
}
