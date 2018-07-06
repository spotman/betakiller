<?php
namespace BetaKiller\Url;

class UrlElementTreeLayerIterator extends \ArrayIterator
{
    /**
     * UrlElementTreeLayerIterator constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface  $tree
     *
     * @param \BetaKiller\Url\IFaceModelInterface|null $parent
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function __construct(UrlElementTreeInterface $tree, IFaceModelInterface $parent = null)
    {
        $layer = $parent
            ? $tree->getChildren($parent)
            : $tree->getRoot();

        parent::__construct($layer, \ArrayObject::STD_PROP_LIST);
    }

    /**
     * @return IFaceModelInterface[]
     */
    public function getArrayCopy(): array
    {
        return parent::getArrayCopy();
    }

    /**
     * @return IFaceModelInterface
     */
    public function current(): IFaceModelInterface
    {
        return parent::current();
    }
}
