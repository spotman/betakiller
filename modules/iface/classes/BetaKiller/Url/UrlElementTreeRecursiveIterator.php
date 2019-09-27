<?php
namespace BetaKiller\Url;

use BetaKiller\Url\ElementFilter\UrlElementFilterInterface;

class UrlElementTreeRecursiveIterator extends UrlElementTreeLayerIterator implements \RecursiveIterator
{
    /**
     * IFace URL elements tree
     *
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * IFace URL element filters
     *
     * @var \BetaKiller\Url\ElementFilter\UrlElementFilterInterface
     */
    private $filters;

    /**
     * UrlElementTreeRecursiveIterator constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface                      $tree
     * @param \BetaKiller\Url\UrlElementInterface|null                     $parent  [optional]
     * @param \BetaKiller\Url\ElementFilter\UrlElementFilterInterface|null $filters [optional]
     *
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        ?UrlElementInterface $parent = null,
        ?UrlElementFilterInterface $filters = null
    ) {
        parent::__construct($tree, $parent, $filters);

        $this->tree    = $tree;
        $this->filters = $filters;
    }

    /**
     * Returns if an iterator can be created for the current entry.
     *
     * @link  http://php.net/manual/en/recursiveiterator.haschildren.php
     * @return bool true if the current entry can be iterated over, otherwise returns false.
     * @since 5.1.0
     */
    public function hasChildren(): bool
    {
        return true;
    }

    /**
     * Returns an iterator for the current entry.
     *
     * @link  http://php.net/manual/en/recursiveiterator.getchildren.php
     * @return \RecursiveIterator An iterator for the current entry.
     * @since 5.1.0
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getChildren(): \RecursiveIterator
    {
        $current = $this->current();

        return new self($this->tree, $current, $this->filters);
    }
}
