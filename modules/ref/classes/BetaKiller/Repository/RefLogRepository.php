<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\RefLog;

/**
 * Class RefLogRepository
 *
 * @package BetaKiller\Repository
 * @method RefLog findById(int $id)
 * @method RefLog create()
 * @method RefLog[] getAll()
 */
class RefLogRepository extends AbstractOrmBasedRepository
{
    /**
     * @param int|null $limit
     *
     * @return RefLog[]
     * @throws \Kohana_Exception
     */
    public function getPending(?int $limit = null): array
    {
        $orm = $this->getOrmInstance();

        $limit = $limit ?? 100;

        if ($limit) {
            $orm->limit($limit);
        }

        return $orm->where('processed', '=', 0)->get_all();
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
