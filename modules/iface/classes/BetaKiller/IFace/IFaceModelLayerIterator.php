<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;

class IFaceModelLayerIterator extends \ArrayIterator
{
    /**
     * IFaceModelLayerIterator constructor.
     *
     * @param \BetaKiller\IFace\IFaceModelInterface|null                  $parent
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate $modelProvider
     */
    public function __construct(IFaceModelInterface $parent = null, IFaceModelProviderAggregate $modelProvider)
    {
        $layer = $modelProvider->getLayer($parent);

        parent::__construct($layer, \ArrayObject::STD_PROP_LIST);
    }

    /**
     * @return IFaceModelInterface[]
     */
    public function getArrayCopy()
    {
        return parent::getArrayCopy();
    }

    /**
     * @return IFaceModelInterface
     */
    public function current()
    {
        return parent::current();
    }
}
