<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;

class IFaceModelRecursiveIterator extends IFaceModelLayerIterator implements \RecursiveIterator
{
    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate
     */
    protected $modelProvider;

    /**
     * IFaceModelLayerIterator constructor.
     *
     * @param \BetaKiller\IFace\IFaceModelInterface|NULL                  $parent
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate $modelProvider
     */
    public function __construct(IFaceModelInterface $parent = null, IFaceModelProviderAggregate $modelProvider)
    {
        $this->modelProvider = $modelProvider;

        parent::__construct($parent, $modelProvider);
    }

    /**
     * Returns if an iterator can be created for the current entry.
     *
     * @link  http://php.net/manual/en/recursiveiterator.haschildren.php
     * @return bool true if the current entry can be iterated over, otherwise returns false.
     * @since 5.1.0
     */
    public function hasChildren()
    {
        return true;
    }

    /**
     * Returns an iterator for the current entry.
     *
     * @link  http://php.net/manual/en/recursiveiterator.getchildren.php
     * @return \RecursiveIterator An iterator for the current entry.
     * @since 5.1.0
     */
    public function getChildren()
    {
        $current = $this->current();

        return new self($current, $this->modelProvider);
    }
}
