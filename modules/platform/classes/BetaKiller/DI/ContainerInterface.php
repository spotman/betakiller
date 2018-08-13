<?php
namespace BetaKiller\DI;

use DI\FactoryInterface;
use Invoker\InvokerInterface;

interface ContainerInterface extends \Psr\Container\ContainerInterface, FactoryInterface, InvokerInterface
{
    /**
     * Inject all dependencies on an existing instance.
     *
     * @param object $instance Object to perform injection upon
     * @throws \InvalidArgumentException
     * @throws \DI\DependencyException Error while injecting dependencies
     * @return object $instance Returns the same instance
     * @todo Remove after dropping Kohana entirely
     * @deprecated
     */
    public function injectOn($instance);
}
