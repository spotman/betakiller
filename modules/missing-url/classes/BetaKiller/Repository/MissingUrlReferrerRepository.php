<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\MissingUrlReferrerModelInterface;

/**
 * Class MissingUrlReferrerRepository
 *
 * @package BetaKiller\Repository
 * @method MissingUrlReferrerModelInterface create()
 */
class MissingUrlReferrerRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $httpReferer
     *
     * @return \BetaKiller\Model\MissingUrlModelInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByHttpReferer(string $httpReferer): ?MissingUrlReferrerModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterHttpReferer($orm, $httpReferer)
            ->findOne($orm);
    }

    private function filterHttpReferer(ExtendedOrmInterface $orm, string $value): self
    {
        $orm->where($orm->object_column('http_referer'), '=', $value);

        return $this;
    }
}
