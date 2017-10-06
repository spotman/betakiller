<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\RefInternalPage;

/**
 * Class RefHitsRepository
 *
 * @package BetaKiller\Repository
 * @method RefInternalPage findById(int $id)
 * @method RefInternalPage create()
 * @method RefInternalPage[] getAll()
 */
class RefInternalPageRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $url
     *
     * @return \BetaKiller\Model\RefInternalPage|null
     * @throws \Kohana_Exception
     */
    public function getByUrl(string $url): ?RefInternalPage
    {
        $orm = $this->getOrmInstance();

        // Internal documents are addressed by path only (no query or fragment coz they are modifiers)
        $path = parse_url($url, PHP_URL_HOST);

        $model = $orm->where('uri', '=', $path)->find();

        return $model->loaded() ? $model : null;
    }
}
