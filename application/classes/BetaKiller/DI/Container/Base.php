<?php
namespace BetaKiller\DI\Container;

use BetaKiller\DI\ContainerInterface;
use DI\DependencyException;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;

use BetaKiller\Utils\Instance\Singleton;
use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;

abstract class Base implements ContainerInterface
{
    use Singleton {
        instance as protected _instance;
    }

    /**
     * @var ContainerInterface
     */
    protected $_container;

    /**
     * @return ContainerInterface
     */
    public static function instance()
    {
        return self::_instance();
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if (!$this->_container) {
            $this->_container = $this->containerFactory();
        }

        return $this->_container;
    }

    /**
     * @return ContainerInterface
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
    public function has($id)
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
    public function make($name, array $parameters = [])
    {
        return $this->getContainer()->make($name, $parameters);
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
    public function call($callable, array $parameters = [])
    {
        return $this->getContainer()->call($callable, $parameters);
    }
}
