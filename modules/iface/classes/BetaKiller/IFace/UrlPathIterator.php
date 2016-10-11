<?php
namespace BetaKiller\IFace;


class UrlPathIterator implements \Iterator, \Countable
{
    /**
     * @var array
     */
    private $array;

    public function __construct($uri) {
        $uri = trim($uri, '/ ');
        $this->array = $uri ? explode('/', $uri) : [];
    }

    function rewind() {
        reset($this->array);
    }

    function current() {
        return current($this->array);
    }

    function key() {
        return key($this->array);
    }

    function next() {
        next($this->array);
    }

    function prev() {
        prev($this->array);
    }

    function valid() {
        return key($this->array) !== null;
    }

    function count() {
        return count($this->array);
    }
}
