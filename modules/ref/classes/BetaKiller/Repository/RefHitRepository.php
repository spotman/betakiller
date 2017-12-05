<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\RefHit;

/**
 * Class RefHitRepository
 *
 * @package BetaKiller\Repository
 * @method RefHit findById(int $id)
 * @method RefHit create()
 * @method RefHit[] getAll()
 */
class RefHitRepository extends AbstractOrmBasedRepository
{
    /**
     * @param int|null $limit
     *
     * @return RefHit[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getPending(?int $limit = null): array
    {
        $limit = $limit ?? 100;

        try {
            $orm = $this->getOrmInstance();

            if ($limit) {
                $orm->limit($limit);
            }

            return $orm->where('processed', '=', 0)->get_all();
        } catch (\Kohana_Exception $e) {
            throw new RepositoryException($e->getMessage(), null, $e->getCode(), $e);
        }
    }

    /**
     * @param int|null $limit
     */
    public function deleteProcessed(?int $limit = null): void
    {
        $orm = $this->getOrmInstance();

        $limit = $limit ?? 100;

        if ($limit) {
            $orm->limit($limit);
        }

        $orm->where('processed', '=', 1)->delete_all();
    }
}
