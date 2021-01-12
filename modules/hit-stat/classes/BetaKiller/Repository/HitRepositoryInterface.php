<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Hit;
use BetaKiller\Model\HitInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * Class HitRepository
 *
 * @package BetaKiller\Repository
 * @method HitInterface findById(int $id)
 * @method HitInterface[] getAll()
 * @method save(HitInterface $entity)
 * @method delete(HitInterface $entity)
 */
interface HitRepositoryInterface extends RepositoryInterface
{
    /**
     * @param \Ramsey\Uuid\UuidInterface $uuid
     *
     * @return \BetaKiller\Model\HitInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByUuid(UuidInterface $uuid): HitInterface;

    /**
     * @param \Ramsey\Uuid\UuidInterface $uuid
     *
     * @return \BetaKiller\Model\HitInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByUuid(UuidInterface $uuid): ?HitInterface;

    /**
     * @param int $limit
     *
     * @return Hit[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getPending(int $limit): array;

    /**
     * @return \BetaKiller\Model\HitInterface|null
     */
    public function getFirstNotProcessed(): ?HitInterface;

    /**
     * @param \DateTimeImmutable $before
     *
     * @return \BetaKiller\Model\HitInterface[]
     */
    public function getUnused(\DateTimeImmutable $before): array;
}
