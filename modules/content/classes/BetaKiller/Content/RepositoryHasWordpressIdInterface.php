<?php
namespace BetaKiller\Content;

interface RepositoryHasWordpressIdInterface
{
    /**
     * @param int $id
     *
     * @return \BetaKiller\Content\EntityHasWordpressIdInterface
     */
    public function find_by_wp_id(int $id): EntityHasWordpressIdInterface;

    /**
     * Returns array of records IDs by their WP IDs
     *
     * @param int[] $wpIDs
     *
     * @return array
     */
    public function find_ids_by_wp_ids(array $wpIDs): array;
}
