<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 09.11.15
 * Time: 16:30
 */

namespace ORM;

use BetaKiller\Model\ExtendedOrmInterface;

class PaginateHelper
{
    /**
     * @var \Paginate
     */
    protected $paginate;

    /**
     * @var int
     */
    protected $itemsPerPage;

    /**
     * @var int
     */
    protected $currentPage;

    /**
     * @var int
     */
    protected $totalItems;

    /**
     * @var int
     */
    protected $totalPages;

    /**
     * PaginateHelper factory.
     *
     * @param ExtendedOrmInterface $model
     * @param int $currentPage
     * @param int $itemsPerPage
     * @return PaginateHelper
     */
    public static function create(ExtendedOrmInterface $model, int $currentPage, int $itemsPerPage): PaginateHelper
    {
        return new self($model, $currentPage, $itemsPerPage);
    }

    /**
     * PaginateHelper constructor.
     *
     * @param ExtendedOrmInterface $model
     * @param int $currentPage
     * @param int $itemsPerPage
     */
    protected function __construct(ExtendedOrmInterface $model, int $currentPage, int $itemsPerPage)
    {
        $this->paginate     = \Paginate::factory($model);
        $this->currentPage  = $currentPage;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return ExtendedOrmInterface[]
     */
    public function getResults(): array
    {
        $start = $this->itemsPerPage * ($this->currentPage - 1);

        /** @var ExtendedOrmInterface[] $results */
        $results = $this->paginate->limit($start, $this->itemsPerPage)->execute()->result()->as_array();

        $this->totalItems = $this->paginate->count_total();
        $this->totalPages = ceil($this->totalItems / $this->itemsPerPage);

        return $results;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return (($this->currentPage + 1) <= $this->getTotalPages());
    }

    /**
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return (($this->currentPage - 1) >= 0);
    }
}
