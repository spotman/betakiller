<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 09.11.15
 * Time: 17:57
 */

namespace BetaKiller\Search;


interface SearchResultsInterface extends \IteratorAggregate, \JsonSerializable
{
    /**
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    public static function emptyResult(): SearchResultsInterface;

    /**
     * @param \BetaKiller\Search\SearchResultsItemInterface $item
     */
    public function addItem(SearchResultsItemInterface $item): void;

    /**
     * @return int
     */
    public function getTotalCount(): int;

    /**
     * @return int
     */
    public function getTotalPages(): int;

    /**
     * @return bool
     */
    public function hasNextPage(): bool;

    /**
     * @return bool
     */
    public function hasPrevPage(): bool;

    /**
     * @return SearchResultsItemInterface[]|\Traversable
     */
    public function getItems();

    /**
     * @param string $url
     */
    public function setUrl(string $url): void;

    /**
     * @return bool
     */
    public function hasUrl(): bool;

    /**
     * @return string
     */
    public function getUrl(): string;
}
