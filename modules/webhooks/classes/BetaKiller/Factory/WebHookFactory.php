<?php
namespace BetaKiller\Factory;

use BetaKiller\Model\WebHookModelInterface;
use BetaKiller\WebHook\WebHookInterface;

class WebHookFactory
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
            ->setClassNamespaces(WebHookInterface::NAMESPACE)
            ->setClassSuffix(WebHookInterface::SUFFIX)
            ->setExpectedInterface(WebHookInterface::class);
    }

    /**
     * @param \BetaKiller\Model\WebHookModelInterface $model
     *
     * @return \BetaKiller\WebHook\WebHookInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFromModel(WebHookModelInterface $model): WebHookInterface
    {
        $codename = $model->getCodename();

        /** @var \BetaKiller\WebHook\WebHookInterface $instance */
        $instance = $this->factory->create($codename);

        return $instance->setModel($model);
    }
}
