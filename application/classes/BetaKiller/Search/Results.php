<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 09.11.15
 * Time: 18:22
 */

namespace BetaKiller\Search;

use BetaKiller\Search\Model\ResultsItem;
use DateTime;
use Traversable;

class Results implements Model\Results, \API_Response_Item
{
    /**
     * @var Model\ResultsItem[]
     */
    protected $items = array();

    /**
     * @var int
     */
    protected $totalCount;

    protected $totalPages;

    protected $hasNextPage;

    protected $url;

    /**
     * Results constructor.
     *
     * @param int $totalCount
     * @param     $totalPages
     * @param     $hasNextPage
     */
    public function __construct($totalCount, $totalPages, $hasNextPage)
    {
        $this->totalCount  = $totalCount;
        $this->totalPages  = $totalPages;
        $this->hasNextPage = $hasNextPage;
    }

    public static function factory($totalItems, $totalPages, $hasNextPage)
    {
        return new self($totalItems, $totalPages, $hasNextPage);
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *        <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function addItem(ResultsItem $item)
    {
        $this->items[] = $item;
    }

    public function getTotalCount()
    {
        return $this->totalCount;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function hasNextPage()
    {
        return $this->hasNextPage;
    }

    public function getURL()
    {
        return $this->url;
    }

    /**
     * @return ResultsItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getItemsData()
    {
        $items = [];

        foreach ($this->items as $item) {
            $items[] = $item->getSearchResultsItemData();
        }

        return $items;
    }

    /**
     * @return array|Traversable
     */
    public function get_api_response_data()
    {
        return [
            'items'         =>  $this->getItemsData(),
            'totalItems'    =>  $this->getTotalCount(),
            'totalPages'    =>  $this->getTotalPages(),
            'hasNextPage'   =>  $this->hasNextPage(),
            'url'           =>  $this->getURL(),
        ];
    }

    /**
     * @return DateTime|NULL
     */
    public function get_api_last_modified()
    {
        // Not done yet
        return null;
    }

    /**
     * @param string $url
     */
    public function setURL($url)
    {
        $this->url = (string) $url;
    }

}
