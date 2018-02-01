<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\MissingUrlModelInterface;
use BetaKiller\Model\MissingUrlRedirectTargetModelInterface;

/**
 * Class MissingUrlRepository
 *
 * @package BetaKiller\Repository
 * @method MissingUrlModelInterface create()
 */
class MissingUrlRepository extends AbstractOrmBasedDispatchableRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return MissingUrlModelInterface::URL_KEY;
    }

    /**
     * @param string $url
     *
     * @return \BetaKiller\Model\MissingUrlModelInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByUrl(string $url): ?MissingUrlModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUrl($orm, $url)
            ->findOne($orm);
    }

    /**
     * @return array
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findWithEmptyTarget(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterTarget($orm, null)
            ->findAll($orm);
    }

    /**
     * @return MissingUrlModelInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getOrderedByTarget(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->orderByTarget($orm)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $url
     *
     * @return \BetaKiller\Repository\MissingUrlRepository
     */
    private function filterUrl(ExtendedOrmInterface $orm, string $url): self
    {
        $orm->where($orm->object_column('url'), '=', $url);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface                        $orm
     * @param \BetaKiller\Model\MissingUrlRedirectTargetModelInterface|null $targetModel
     *
     * @return \BetaKiller\Repository\MissingUrlRepository
     */
    private function filterTarget(ExtendedOrmInterface $orm, ?MissingUrlRedirectTargetModelInterface $targetModel): self
    {
        if ($targetModel) {
            $orm->where($orm->object_column('redirect_to'), '=', $targetModel->getID());
        } else {
            $orm->where($orm->object_column('redirect_to'), 'IS', null);
        }

        return $this;
    }

    private function orderByTarget(ExtendedOrmInterface $orm): self
    {
        $orm->order_by($orm->object_column('redirect_to'), 'ASC');

        return $this;
    }
}
