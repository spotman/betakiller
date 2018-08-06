<?php
namespace BetaKiller\Url;

class UrlElementTreeLayerIterator extends \FilterIterator
{
    /**
     * IFace URL element filters
     *
     * @var \BetaKiller\Url\UrlElementFilterInterface
     */
    private $filters;

    /**
     * UrlElementTreeLayerIterator constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface        $tree
     * @param \BetaKiller\Url\UrlElementInterface            $parent
     * @param \BetaKiller\Url\UrlElementFilterInterface|null $filters [optional]
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        ?UrlElementInterface $parent = null,
        ?UrlElementFilterInterface $filters = null
    ) {
        $this->filters = $filters;

        $layer = $parent
            ? $tree->getChildren($parent)
            : $tree->getRoot();

        $layer = new \ArrayIterator($layer);
        parent::__construct($layer);
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface
     */
    public function current(): UrlElementInterface
    {
        return parent::current();
    }

    /**
     * Checking availability IFace URL element by filters
     *
     * @return bool
     */
    public function accept(): bool
    {
        if ($this->filters) {
            $urlElement = $this->current();

            return $this->filters->isAvailable($urlElement);
        }

        return true;
    }
}
