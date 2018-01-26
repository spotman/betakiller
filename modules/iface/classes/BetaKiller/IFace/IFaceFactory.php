<?php
namespace BetaKiller\IFace;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

class IFaceFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * IFaceFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->cacheInstances()
            ->setClassNamespaces('IFace')
            ->setExpectedInterface(IFaceInterface::class);
    }

    /**
     * Creates instance of IFace from model
     *
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromModel(IFaceModelInterface $model): IFaceInterface
    {
        $codename = $model->getCodename();

        /** @var \BetaKiller\IFace\IFaceInterface $instance */
        $instance = $this->factory->create($codename);

        return $instance->setModel($model);
    }
}
