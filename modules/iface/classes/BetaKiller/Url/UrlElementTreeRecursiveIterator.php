<?php
namespace BetaKiller\Url;

class UrlElementTreeRecursiveIterator extends UrlElementTreeLayerIterator implements \RecursiveIterator
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * UrlElementTreeRecursiveIterator constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface  $tree
     * @param \BetaKiller\Url\UrlElementInterface|null $parent
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function __construct(UrlElementTreeInterface $tree, ?UrlElementInterface $parent = null)
    {
        parent::__construct($tree, $parent);

        $this->tree = $tree;
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getChildren(): \RecursiveIterator
    {
        $current = $this->current();

        return new self($this->tree, $current);
    }
}
