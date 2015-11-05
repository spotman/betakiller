<?php
namespace BetaKiller\URL\QueryConverter;

/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 02.11.15
 * Time: 15:45
 */

interface Convertible extends \IteratorAggregate
{
    /**
     * Returns array of allowed keys
     * @return array
     */
    public function getAllowedUrlQueryKeys();

    /**
     * @param string $key
     * @return ConvertibleItem
     */
    public function createItemFromQueryKey($key);

    /**
     * @return string
     */
    public function getUrlQueryKeysNamespace();

    /**
     * Retrieve an external iterator for set of ConvertibleItem`s
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     */
    public function getIterator();
}
