<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 09.11.15
 * Time: 18:22
 */

namespace BetaKiller\Search;

use Traversable;

class SearchResults implements SearchResultsInterface
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
    public function __construct(array $items, int $totalCount, int $totalPages, bool $hasNextPage, string $url = null)
    {
        $this->totalCount  = $totalCount;
        $this->totalPages  = $totalPages;
        $this->hasNextPage = $hasNextPage;
        $this->url         = $url;

        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * @param string|null $url
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    public static function emptyResult(string $url = null): SearchResultsInterface
    {
        return new self([], 0, 0, false, $url);
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

    public function getURL(): ?string
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
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'items_found'   => $this->getItemsData(),
            'total_items'   => $this->getTotalCount(),
            'total_pages'   => $this->getTotalPages(),
            'has_next_page' => $this->hasNextPage(),
            'url'           => $this->getURL(),
        ];
    }

    /**
     * @param string $url
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
    }
}
