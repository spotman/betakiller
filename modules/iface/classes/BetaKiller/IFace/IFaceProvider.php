<?php
namespace BetaKiller\IFace;

use BetaKiller\Factory\FactoryException;
use BetaKiller\IFace\Exception\IFaceException;

class IFaceProvider
{
    /**
     * @var \BetaKiller\IFace\IFaceFactory
     */
    protected $factory;

    /**
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * IFaceProvider constructor
     *
     * @param \BetaKiller\IFace\IFaceModelTree $tree
     * @param \BetaKiller\IFace\IFaceFactory   $factory
     */
    public function __construct(IFaceModelTree $tree, IFaceFactory $factory)
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function fromCodename(string $codename): IFaceInterface
    {
        $model = $this->tree->getByCodename($codename);

        return $this->fromModel($model);
    }

    /**
     * Creates IFace instance from it`s model
     *
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function fromModel(IFaceModelInterface $model): IFaceInterface
    {
        try {
            return $this->factory->createFromModel($model);
        } catch (FactoryException $e) {
            throw IFaceException::wrap($e);
        }
    }
}
