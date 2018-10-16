<?php
namespace BetaKiller\IFace;

use BetaKiller\Factory\FactoryException;
use BetaKiller\Factory\IFaceFactory;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementTreeInterface;

class IFaceProvider
{
    /**
     * @var \BetaKiller\Factory\IFaceFactory
     */
    protected $factory;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * IFaceProvider constructor
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Factory\IFaceFactory        $factory
     */
    public function __construct(UrlElementTreeInterface $tree, IFaceFactory $factory)
    {
        $this->factory = $factory;
        $this->tree    = $tree;
    }

    /**
     * Creates IFace instance from it`s codename (automatic model detection)
     *
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function fromCodename(string $codename): IFaceInterface
    {
        $model = $this->tree->getByCodename($codename);

        return $this->fromUrlElement($model);
    }

    /**
     * Creates IFace instance from URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function fromUrlElement(UrlElementInterface $model): IFaceInterface
    {
        try {
            return $this->factory->createFromUrlElement($model);
        } catch (FactoryException $e) {
            throw UrlElementException::wrap($e);
        }
    }
}
