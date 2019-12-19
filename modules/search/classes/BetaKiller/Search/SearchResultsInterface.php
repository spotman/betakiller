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
     * @param string|null $url
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     */
    public static function emptyResult(string $url = null): SearchResultsInterface;

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
     * @return string
     */
    public function getURL(): ?string;

    /**
     * @return SearchResultsItemInterface[]|\Traversable
     */
    public function getItems();

    /**
     * @return array
     */
    public function getItemsData(): array;

    /**
     * @param string $url
     */
    public function setURL(string $url): void;
}
