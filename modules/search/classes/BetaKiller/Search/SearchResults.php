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
    protected array $items = [];

    /**
     * @var int
     */
    protected int $totalCount;

    /**
     * @var int
     */
    protected int $totalPages;

    /**
     * @var bool
     */
    private bool $hasPrevPage;

    /**
     * @var bool
     */
    protected bool $hasNextPage;

    /**
     * @var string|null
     */
    protected ?string $url = null;

    /**
     * SearchResults constructor.
     *
     * @param array $items
     * @param int   $totalCount
     * @param int   $totalPages
     * @param bool  $hasPrevPage
     * @param bool  $hasNextPage
     */
    public function __construct(array $items, int $totalCount, int $totalPages, bool $hasPrevPage, bool $hasNextPage)
    {
        $this->totalCount  = $totalCount;
        $this->totalPages  = $totalPages;
        $this->hasPrevPage = $hasPrevPage;
        $this->hasNextPage = $hasNextPage;

        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * @inheritDoc
     */
    public static function emptyResult(): SearchResultsInterface
    {
        return new self([], 0, 0, false, false);
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

    /**
     * @inheritDoc
     */
    public function addItem(SearchResultsItemInterface $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @inheritDoc
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @inheritDoc
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @inheritDoc
     */
    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    /**
     * @inheritDoc
     */
    public function hasPrevPage(): bool
    {
        return $this->hasPrevPage;
    }

    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritDoc
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    public function hasUrl(): bool
    {
        return $this->url !== null;
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): mixed
    {
        return [
            'items_found'   => $this->getItems(),
            'total_items'   => $this->getTotalCount(),
            'total_pages'   => $this->getTotalPages(),
            'has_prev_page' => $this->hasPrevPage(),
            'has_next_page' => $this->hasNextPage(),
            'url'           => $this->hasUrl() ? $this->getUrl() : null,
        ];
    }
}
