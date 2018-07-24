<?php
namespace BetaKiller\Url;

class UrlElementTreeLayerIterator extends \ArrayIterator
{
    /**
     * UrlElementTreeLayerIterator constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     *
     * @param \BetaKiller\Url\UrlElementInterface     $parent
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function __construct(UrlElementTreeInterface $tree, UrlElementInterface $parent = null)
    {
        $layer = $parent
            ? $tree->getChildren($parent)
            : $tree->getRoot();

        parent::__construct($layer, \ArrayObject::STD_PROP_LIST);
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getArrayCopy(): array
    {
        return parent::getArrayCopy();
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface
     */
    public function current(): UrlElementInterface
    {
        return parent::current();
    }
}
