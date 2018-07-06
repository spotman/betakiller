<?php
namespace BetaKiller\Factory;

use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;

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
     *
     * @throws \BetaKiller\Factory\FactoryException
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
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromUrlElement(UrlElementInterface $model): IFaceInterface
    {
        if (! $model instanceof IFaceModelInterface) {
            throw new FactoryException('Can not create IFace from URL element :codename of type :class', [
                ':codename' => $model->getCodename(),
                ':class' => \get_class($model),
            ]);
        }

        $codename = $model->getCodename();

        /** @var \BetaKiller\IFace\IFaceInterface $instance */
        $instance = $this->factory->create($codename);

        return $instance->setModel($model);
    }
}
