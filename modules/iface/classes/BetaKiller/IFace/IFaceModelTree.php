<?php
namespace BetaKiller\IFace;

use URL_Prototype;

class IFaceModelTree
{
    /**
     * @var \IFace_Model_Provider
     */
    protected $_model_provider;

    public function __construct(\IFace_Model_Provider $model_provider)
    {
        $this->_model_provider = $model_provider;
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return IFaceModelRecursiveIterator|IFaceModelInterface[]
     */
    public function getRecursiveIterator(IFaceModelInterface $parent = NULL)
    {
        return new IFaceModelRecursiveIterator($parent, $this->_model_provider);
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     */
    public function getRecursiveIteratorIterator(IFaceModelInterface $parent = NULL)
    {
        return new \RecursiveIteratorIterator($this->getRecursiveIterator($parent), \RecursiveIteratorIterator::SELF_FIRST);
    }
}
