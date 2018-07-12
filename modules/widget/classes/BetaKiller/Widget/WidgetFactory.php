<?php
namespace BetaKiller\Widget;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;

class WidgetFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * WidgetFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder)
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
