<?php
namespace BetaKiller\Acl;

use BetaKiller\DI\ContainerInterface;
use Spotman\Acl\ResourceFactory\ResourceFactoryInterface;
use Spotman\Acl\ResourceInterface;
use Spotman\Acl\Exception;

class ResourceFactory implements ResourceFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ResourceFactory constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $identity
     *
     * @return ResourceInterface
     * @throws Exception
     */
    public function createResource($identity)
    {
        // TODO common namespace factory

        $ns = "\\BetaKiller\\Acl\\Resource\\";
        $className = $ns.ucfirst($identity).'Resource';

        if (!class_exists($className)) {
            throw new Exception('Class :name does not exists', [':name' => $className]);
        }

        return $this->container->get($className);
    }
}
