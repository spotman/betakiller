<?php
namespace BetaKiller\URL\QueryConverter;

/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 02.11.15
 * Time: 15:45
 */

interface ConvertibleInterface extends \IteratorAggregate
{
    /**
     * @param string $key
     *
     * @return ConvertibleItemInterface
     */
    public function getItemByQueryKey(string $key): ConvertibleItemInterface;

    /**
     * @return string
     */
    public function getUrlQueryKeysNamespace(): string;

    /**
     * Retrieve an external iterator for set of ConvertibleItemInterface`s
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     */
    public function getIterator();
}
