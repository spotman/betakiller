<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Hit;
use BetaKiller\Model\HitInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Ramsey\Uuid\UuidInterface;
use Worknector\Repository\CreatedAtByRepositoryTrait;

/**
 * Class HitRepository
 *
 * @package BetaKiller\Repository
 * @method HitInterface findById(int $id)
 * @method HitInterface[] getAll()
 * @method save(HitInterface $entity)
 */
class HitRepository extends AbstractOrmBasedRepository implements HitRepositoryInterface
{
    use CreatedAtByRepositoryTrait;

    /**
     * @param \Ramsey\Uuid\UuidInterface $uuid
     *
     * @return \BetaKiller\Model\HitInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByUuid(UuidInterface $uuid): HitInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUuid($orm, $uuid)
            ->getOne($orm);
    }

    /**
     * @param \Ramsey\Uuid\UuidInterface $uuid
     *
     * @return \BetaKiller\Model\HitInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByUuid(UuidInterface $uuid): ?HitInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUuid($orm, $uuid)
            ->findOne($orm);
    }

    /**
     * @param int $limit
     *
     * @return Hit[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getPending(int $limit): array
    {
        $limit = $limit ?? 100;

        $orm = $this->getOrmInstance();

        if ($limit) {
            $orm->limit($limit);
        }

        return $this
            ->filterProcessed($orm, false)
            ->findAll($orm);
    }

    public function getFirstNotProcessed(): ?HitInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterProcessed($orm, false)
            ->orderByCreatedAt($orm)
            ->findOne($orm);
    }

    /**
     * @inheritDoc
     */
    public function getUnused(\DateTimeImmutable $before): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterProcessed($orm, true)
            ->filterProtected($orm, false)
            ->filterCreatedAtBefore($orm, $before)
            ->limit($orm, 300)
            ->findAll($orm);
    }

    private function filterProcessed(OrmInterface $orm, bool $value): self
    {
        $orm->where(Hit::COL_IS_PROCESSED, '=', $value);

        return $this;
    }

    private function filterProtected(OrmInterface $orm, bool $value): self
    {
        $orm->where(Hit::COL_IS_PROTECTED, '=', $value);

        return $this;
    }

    private function filterUuid(OrmInterface $orm, UuidInterface $uuid): self
    {
        $orm->where(Hit::COL_UUID, '=', $uuid->toString());

        return $this;
    }

    protected function getCreatedByColumnName(): string
    {
        return Hit::getCreatedByColumnName();
    }

    protected function getCreatedAtColumnName(): string
    {
        return Hit::getCreatedAtColumnName();
    }
}
