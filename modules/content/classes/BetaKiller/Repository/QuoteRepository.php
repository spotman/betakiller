<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Quote;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateTimeInterface;

class QuoteRepository extends AbstractOrmBasedRepository
{
    /**
     * Creates empty entity
     *
     * @return mixed
     */
    public function create(): Quote
    {
        return parent::create();
    }

    /**
     * @param \DateTimeInterface|null $before
     *
     * @return Quote|mixed
     */
    public function getLatestQuote(DateTimeInterface $before = NULL): Quote
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
