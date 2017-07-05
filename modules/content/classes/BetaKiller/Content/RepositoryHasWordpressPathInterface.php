<?php
namespace BetaKiller\Content;

interface RepositoryHasWordpressPathInterface
{
    /**
     * @param string $wp_path
     * @return EntityHasWordpressPathInterface|null
     */
    public function find_by_wp_path($wp_path): ?EntityHasWordpressPathInterface;
}
