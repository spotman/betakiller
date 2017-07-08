<?php
namespace BetaKiller\Repository;

/**
 * Interface RepositoryHasWordpressIdInterface
 *
 * @package BetaKiller\Content
 */
interface RepositoryHasWordpressIdInterface
{
    /**
     * @param int $id
     *
     * @return \BetaKiller\Model\EntityHasWordpressIdInterface|mixed|null
     */
    public function findByWpID(int $id);

    /**
     * Returns array of records IDs by their WP IDs
     *
     * @param int[] $wpIDs
     *
     * @return array
     */
    public function findIDsByWpIDs(array $wpIDs): array;
}
