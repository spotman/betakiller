<?php
namespace BetaKiller\IFace;

use BetaKiller\Factory\NamespaceBasedFactory;

class IFaceFactory
{
    /**
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $provider;

    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * IFaceFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
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
