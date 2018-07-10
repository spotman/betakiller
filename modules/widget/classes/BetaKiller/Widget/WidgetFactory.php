<?php
namespace BetaKiller\Widget;

use BetaKiller\Factory\NamespaceBasedFactoryBuilder;
use Psr\Log\LoggerInterface;

class WidgetFactory
{
    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * WidgetFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactoryBuilder $factoryBuilder
     * @param \Psr\Log\LoggerInterface                         $logger
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function __construct(NamespaceBasedFactoryBuilder $factoryBuilder, LoggerInterface $logger)
    {
        $this->factory = $factoryBuilder
            ->createFactory()
            ->setClassNamespaces('Widget')
            ->setClassSuffix('Widget')
            ->setExpectedInterface(WidgetInterface::class);

        $this->logger = $logger;
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
        $instance->setLogger($this->logger);

        return $instance;
    }
}
