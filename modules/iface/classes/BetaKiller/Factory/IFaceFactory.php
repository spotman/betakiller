<?php
namespace BetaKiller\Factory;

use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;

class IFaceFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * IFaceFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->cacheInstances()
            ->setClassNamespaces(IFaceInterface::NAMESPACE)
            ->setClassSuffix(IFaceInterface::SUFFIX)
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
        if (!$model instanceof IFaceModelInterface) {
            throw new FactoryException('Can not create IFace from URL element :codename of type :class', [
                ':codename' => $model->getCodename(),
                ':class'    => get_class($model),
            ]);
        }

        $codename = $model->getCodename();

        return $this->factory->create($codename);
    }
}
