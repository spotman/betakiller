<?php
namespace BetaKiller\IFace;


class IFaceModelRecursiveIterator extends IFaceModelLayerIterator implements \RecursiveIterator
{
    /**
     * @var \IFace_Model_Provider
     */
    protected $_model_provider;

    /**
     * IFaceModelLayerIterator constructor.
     *
     * @param \BetaKiller\IFace\IFaceModelInterface|NULL $parent
     * @param \IFace_Model_Provider                      $model_provider
     */
    public function __construct(IFaceModelInterface $parent = NULL, \IFace_Model_Provider $model_provider)
    {
        $this->_model_provider = $model_provider;

        parent::__construct($parent, $model_provider);
    }

    /**
     * Returns if an iterator can be created for the current entry.
     * @link http://php.net/manual/en/recursiveiterator.haschildren.php
     * @return bool true if the current entry can be iterated over, otherwise returns false.
     * @since 5.1.0
     */
    public function hasChildren()
    {
        return TRUE;
    }

    /**
     * Returns an iterator for the current entry.
     * @link http://php.net/manual/en/recursiveiterator.getchildren.php
     * @return \RecursiveIterator An iterator for the current entry.
     * @since 5.1.0
     */
    public function getChildren()
    {
        $current = $this->current();

        return new self($current, $this->_model_provider);
    }
}
