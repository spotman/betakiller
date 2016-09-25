<?php
namespace BetaKiller\DI\Container;

use BetaKiller\DI\Container;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;

use BetaKiller\Utils\Instance\Singleton;

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
     * @return Container
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
}
