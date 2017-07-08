<?php
namespace BetaKiller\DI\Container;

use BetaKiller\DI\ContainerInterface;
use DI\DependencyException;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;

use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;

abstract class Base implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    protected static $instance;

    /**
     * @var ContainerInterface
     */
    protected $_container;

    /**
     * @return ContainerInterface
     * @deprecated Bad practice, use DI in constructor instead
     */
    public static function getInstance(): ContainerInterface
    {
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * You can`t create objects directly, use CLASS::instance() instead
     * Also you can define your own protected constructor in child class
     */
    protected function __construct() {}

    /**
     * @return ContainerInterface|mixed
     */
    protected function getContainer()
    {
        if (!$this->_container) {
            $this->_container = $this->containerFactory();
        }

        return $this->_container;
    }

    /**
     * @return ContainerInterface|mixed
     */
    abstract protected function containerFactory();

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id): bool
    {
        return $this->getContainer()->has($id);
    }

    /**
     * Resolves an entry by its name. If given a class name, it will return a new instance of that class.
     *
     * @param string $name Entry name or a class name.
     * @param array  $parameters Optional parameters to use to build the entry. Use this to force specific
     *                           parameters to specific values. Parameters not defined in this array will
     *                           be automatically resolved.
     *
     * @throws \InvalidArgumentException The name parameter must be of type string.
     * @throws DependencyException       Error while resolving the entry.
     * @throws \DI\NotFoundException         No entry or class found for the given name.
     * @return mixed
     */
    public function make($name, array $parameters = null)
    {
        return $this->getContainer()->make($name, $parameters ?? []);
    }

    /**
     * Call the given function using the given parameters.
     *
     * @param callable $callable Function to call.
     * @param array    $parameters Parameters to use.
     *
     * @return mixed Result of the function.
     *
     * @throws InvocationException Base exception class for all the sub-exceptions below.
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function call($callable, array $parameters = null)
    {
        return $this->getContainer()->call($callable, $parameters ?: []);
    }

    /**
     * Inject all dependencies on an existing instance.
     *
     * @param mixed $instance Object to perform injection upon
     *
     * @throws \InvalidArgumentException
     * @throws \DI\DependencyException Error while injecting dependencies
     * @return mixed $instance Returns the same instance
     */
    public function injectOn($instance)
    {
        return $this->getContainer()->injectOn($instance);
    }
}
