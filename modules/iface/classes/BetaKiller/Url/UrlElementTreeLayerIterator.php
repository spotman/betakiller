<?php
namespace BetaKiller\Url;

use BetaKiller\Url\ElementFilter\UrlElementFilterInterface;

class UrlElementTreeLayerIterator extends \FilterIterator
{
    /**
     * IFace URL element filters
     *
     * @var \BetaKiller\Url\ElementFilter\UrlElementFilterInterface|null
     */
    private $filter;

    /**
     * UrlElementTreeLayerIterator constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface                      $tree
     * @param \BetaKiller\Url\UrlElementInterface                          $parent
     * @param \BetaKiller\Url\ElementFilter\UrlElementFilterInterface|null $filter [optional]
     *
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        ?UrlElementInterface $parent = null,
        ?UrlElementFilterInterface $filter = null
    ) {
        $this->filter = $filter;

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
        if ($this->filter) {
            $urlElement = $this->current();

            return $this->filter->isAvailable($urlElement);
        }

        return true;
    }
}
