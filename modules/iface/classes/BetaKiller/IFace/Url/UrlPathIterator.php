<?php
namespace BetaKiller\IFace\Url;

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

    public function rewind() {
        reset($this->array);
    }

    public function current() {
        return current($this->array);
    }

    public function key() {
        return key($this->array);
    }

    public function next() {
        next($this->array);
    }

    public function prev() {
        prev($this->array);
    }

    public function valid() {
        return key($this->array) !== null;
    }

    public function count() {
        return count($this->array);
    }
}
