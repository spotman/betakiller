<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\ContentPostRevision;
use BetaKiller\Model\RevisionModelInterface;
use BetaKiller\Model\RevisionRepositoryInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateTimeInterface;

/**
 * Class ContentPostRevisionRepository
 *
 * @package BetaKiller\Repository
 * @method RevisionModelInterface findById(int $id)
 * @method RevisionModelInterface create()
 * @method RevisionModelInterface[] getAll()
 */
class ContentPostRevisionRepository extends AbstractOrmBasedDispatchableRepository implements
    RevisionRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return ContentPostRevision::URL_KEY_NAME;
    }

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $compareTo
     *
     * @return \BetaKiller\Model\RevisionModelInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getPreviousRevision(RevisionModelInterface $compareTo): ?RevisionModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterRelatedEntity($orm, $compareTo->getRevisionRelatedEntity())
            ->filterBeforeCreatedAt($orm, $compareTo->getCreatedAt())
            ->orderByCreatedAt($orm)
            ->findOne($orm);
    }

    private function filterRelatedEntity(OrmInterface $orm, AbstractEntityInterface $entity): self
    {
        $orm->where('post_id', '=', $entity->getID());

        return $this;
    }

    private function orderByCreatedAt(OrmInterface $orm, ?bool $asc = null): self
    {
        $orm->order_by('created_at', $asc ? 'ASC' : 'DESC');

        return $this;
    }

    private function filterBeforeCreatedAt(OrmInterface $orm, \DateTimeImmutable $before): self
    {
        $orm->filter_datetime_column_value('created_at', $before, '<');

        return $this;
    }
}
