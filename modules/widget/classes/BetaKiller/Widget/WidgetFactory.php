<?php
namespace BetaKiller\Widget;

use BetaKiller\Factory\NamespaceBasedFactory;
use Psr\Log\LoggerInterface;

class WidgetFactory
{
    /**
     * @var NamespaceBasedFactory
     */
    private $factory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * WidgetFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     * @param \Psr\Log\LoggerInterface                  $logger
     */
    public function __construct(NamespaceBasedFactory $factory, LoggerInterface $logger)
    {
        $this->factory = $factory
            ->setClassNamespaces('Widget')
            ->setClassSuffix('Widget')
            ->setExpectedInterface(WidgetInterface::class);

        $this->logger = $logger;
    }

    /**
     * @param string        $name
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
