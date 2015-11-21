<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 09.11.15
 * Time: 17:57
 */

namespace BetaKiller\Search\Model;


interface Results extends \IteratorAggregate
{
    public function addItem(ResultsItem $item);

    /**
     * @return int
     */
    public function getTotalCount();

    /**
     * @return int
     */
    public function getTotalPages();

    /**
     * @return bool
     */
    public function hasNextPage();

    /**
     * @return string
     */
    public function getURL();

    /**
     * @return ResultsItem[]
     */
    public function getItems();

    /**
     * @return array
     */
    public function getItemsData();

    /**
     * @param string $url
     */
    public function setURL($url);
}
