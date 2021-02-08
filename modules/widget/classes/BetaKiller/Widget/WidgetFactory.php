<?php
namespace BetaKiller\Widget;

use BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface;
use BetaKiller\Factory\NamespaceBasedFactoryInterface;

class WidgetFactory
{
    /**
     * @var NamespaceBasedFactoryInterface
     */
    private NamespaceBasedFactoryInterface $factory;

    /**
     * WidgetFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilderInterface $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilderInterface $factoryBuilder)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces('Widget')
            ->setClassSuffix('Widget')
            ->setExpectedInterface(WidgetInterface::class);
    }

    /**
     * @param string $name
     *
     * @return WidgetInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $name): WidgetInterface
    {
        /** @var WidgetInterface $instance */
        $instance = $this->factory->create($name);

        $instance->setName($name);

        return $instance;
    }
}
