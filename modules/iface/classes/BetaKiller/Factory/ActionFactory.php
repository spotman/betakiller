<?php
namespace BetaKiller\Factory;

use BetaKiller\Action\ActionInterface;
use BetaKiller\Url\ActionModelInterface;
use BetaKiller\Url\UrlElementInterface;

class ActionFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * WebHookFactory constructor.
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
            ->setClassNamespaces(ActionInterface::NAMESPACE)
            ->setClassSuffix(ActionInterface::SUFFIX)
            ->setExpectedInterface(ActionInterface::class);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \BetaKiller\Action\ActionInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromUrlElement(UrlElementInterface $model): ActionInterface
    {
        if (!$model instanceof ActionModelInterface) {
            throw new FactoryException('Can not create Action from URL element :codename of type :class', [
                ':codename' => $model->getCodename(),
                ':class'    => \get_class($model),
            ]);
        }

        $codename = $model->getCodename();

        return $this->factory->create($codename);
    }
}
