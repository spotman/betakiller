<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 09.11.15
 * Time: 18:22
 */

namespace BetaKiller\Search;

use Spotman\Api\ApiResponseItemInterface;
use Traversable;

class SearchResults implements SearchResultsInterface, ApiResponseItemInterface
{
    /**
     * @var \BetaKiller\Search\SearchResultsItemInterface[]
     */
    protected $items = [];

    /**
     * @var int
     */
    protected $totalCount;

    /**
     * @var int
     */
    protected $totalPages;

    /**
     * @var bool
     */
    protected $hasNextPage;

    /**
     * @var string
     */
    private $url;

    /**
     * SearchResults constructor.
     *
     * @param array $items
     * @param int   $totalCount
     * @param int   $totalPages
     * @param bool  $hasNextPage
     */
    public function __construct(array $items, int $totalCount, int $totalPages, bool $hasNextPage)
    {
        $this->totalCount  = $totalCount;
        $this->totalPages  = $totalPages;
        $this->hasNextPage = $hasNextPage;

        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public static function factory(array $items, int $totalItems, int $totalPages, bool $hasNextPage): SearchResultsInterface
    {
        return new self($items, $totalItems, $totalPages, $hasNextPage);
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *        <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function addItem(SearchResultsItemInterface $item): void
    {
        $this->items[] = $item;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @return SearchResultsItemInterface[]|\Traversable
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getItemsData(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            $items[] = $item->getSearchResultsItemData();
        }

        return $items;
    }

    /**
     * @return callable
     */
    public function getApiResponseData(): callable
    {
        return function() {
            return [
                'items'       => $this->getItemsData(),
                'totalItems'  => $this->getTotalCount(),
                'totalPages'  => $this->getTotalPages(),
                'hasNextPage' => $this->hasNextPage(),
                'url'         => $this->getURL(),
            ];
        };
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getApiLastModified(): \DateTimeImmutable
    {
        // Not done yet
        return null;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
    }
}
