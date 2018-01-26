<?php
namespace BetaKiller\IFace;

class IFaceModelRecursiveIterator extends IFaceModelLayerIterator implements \RecursiveIterator
{
    /**
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * IFaceModelLayerIterator constructor.
     *
     * @param \BetaKiller\IFace\IFaceModelTree           $tree
     *
     * @param \BetaKiller\IFace\IFaceModelInterface|NULL $parent
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function __construct(IFaceModelTree $tree, IFaceModelInterface $parent = null)
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
