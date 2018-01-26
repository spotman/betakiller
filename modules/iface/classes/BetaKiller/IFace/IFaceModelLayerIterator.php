<?php
namespace BetaKiller\IFace;

class IFaceModelLayerIterator extends \ArrayIterator
{
    /**
     * IFaceModelLayerIterator constructor.
     *
     * @param \BetaKiller\IFace\IFaceModelTree           $tree
     *
     * @param \BetaKiller\IFace\IFaceModelInterface|null $parent
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function __construct(IFaceModelTree $tree, IFaceModelInterface $parent = null)
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
