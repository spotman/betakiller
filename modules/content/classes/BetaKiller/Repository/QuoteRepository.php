<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\QuoteInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateTimeInterface;

/**
 * Class QuoteRepository
 *
 * @package BetaKiller\Repository
 * @method QuoteInterface findById(int $id)
 * @method QuoteInterface findByWpId(int $id)
 * @method QuoteInterface create()
 * @method QuoteInterface[] getAll()
 */
class QuoteRepository extends AbstractOrmBasedRepository implements RepositoryHasWordpressIdInterface
{
    use OrmBasedRepositoryHasWordpressIdTrait;

    /**
     * @param \DateTimeInterface|null $before
     *
     * @return \BetaKiller\Model\QuoteInterface|mixed
     */
    public function getLatestQuote(DateTimeInterface $before = null): QuoteInterface
    {
        $orm = $this->getOrmInstance();

        if ($before) {
            $this->filterBeforeCreatedAt($orm, $before);
        }

        $this->orderByCreatedAt($orm);

        return $orm->limit(1)->find();
    }

    private function orderByCreatedAt(OrmInterface $orm, ?bool $asc = null): void
    {
        $orm->order_by('created_at', $asc ? 'ASC' : 'DESC');
    }

    private function filterBeforeCreatedAt(OrmInterface $orm, DateTimeInterface $before): void
    {
        $orm->filter_datetime_column_value('created_at', $before, '<');
    }
}
