<?php
namespace BetaKiller\Factory;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;

class OrmFactory extends NamespaceBasedFactory
{
    protected function init()
    {
        $this
            ->setExpectedInterface(OrmInterface::class)
            ->setClassPrefixes('Model');
    }

//    /**
//     * @param string     $className
//     * @param array|null $arguments
//     *
//     * @return \BetaKiller\Utils\Kohana\ORM\OrmInterface
//     */
//    protected function createInstance($className, array $arguments = null)
//    {
//        $id = $arguments ? array_shift($arguments) : null;
//
//        return new $className($id);
//    }
}
