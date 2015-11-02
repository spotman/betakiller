<?php
namespace BetaKiller\DI;

use Interop\Container\ContainerInterface;
use BetaKiller\DI\Container;

trait Mixin
{
    /**
     * @return \BetaKiller\Model\User
     */
    protected function getCurrentUser()
    {
        return $this->getContainer()->get('User');
    }

    /**
     * Use this method for creating DI helpers inside of root classes (Controller, API model, etc)
     * DO NOT use it in every class where you need DI, use autowiring and annotations instead
     *
     * @example function getUser() { return $this->getContainer()->get('User'); }
     *
     * @url http://php-di.org/doc/best-practices.html
     * @return ContainerInterface
     */
    final protected function getContainer()
    {
        return Container::instance();
    }
}
