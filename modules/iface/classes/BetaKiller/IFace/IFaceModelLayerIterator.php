<?php
namespace BetaKiller\IFace;


class IFaceModelLayerIterator extends \ArrayIterator
{
    /**
     * IFaceModelLayerIterator constructor.
     *
     * @param \BetaKiller\IFace\IFaceModelInterface|NULL $parent
     * @param \IFace_Model_Provider                      $model_provider
     */
    public function __construct(IFaceModelInterface $parent = NULL, \IFace_Model_Provider $model_provider)
    {
        $layer = $model_provider->get_layer($parent);

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
