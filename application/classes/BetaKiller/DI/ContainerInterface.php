<?php
namespace BetaKiller\DI;

use DI\FactoryInterface;
use DI\InvokerInterface;

interface ContainerInterface extends \Interop\Container\ContainerInterface, FactoryInterface, InvokerInterface
{
    /**
     * Inject all dependencies on an existing instance.
     *
     * @param object $instance Object to perform injection upon
     * @throws \InvalidArgumentException
     * @throws \DI\DependencyException Error while injecting dependencies
     * @return object $instance Returns the same instance
     * @deprecated Remove after dropping Kohana entirely
     */
    public function injectOn($instance);
}
