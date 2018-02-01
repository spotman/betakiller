<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\MissingUrlRedirectTargetModelInterface;

/**
 * Class MissingUrlRedirectTargetRepository
 *
 * @package BetaKiller\Repository
 * @method MissingUrlRedirectTargetModelInterface create()
 */
class MissingUrlRedirectTargetRepository extends AbstractOrmBasedDispatchableRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return MissingUrlRedirectTargetModelInterface::URL_KEY;
    }

    /**
     * @param string $url
     *
     * @return \BetaKiller\Model\MissingUrlRedirectTargetModelInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByUrl(string $url): ?MissingUrlRedirectTargetModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUrl($orm, $url)
            ->findOne($orm);
    }

    private function filterUrl(ExtendedOrmInterface $orm, string $url): self
    {
        $orm->where($orm->object_column('url'), '=', $url);

        return $this;
    }
}
