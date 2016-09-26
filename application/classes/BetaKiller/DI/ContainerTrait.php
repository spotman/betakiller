<?php
namespace BetaKiller\DI;

use BetaKiller\DI\Container;
use BetaKiller\DI\ContainerInterface;

trait ContainerTrait
{
    /**
     * Override this trait in your application directory and place helpers in it
     */

    /**
     * Use this method for creating DI helpers inside of root classes (Controller, API model, etc)
     * DO NOT use it in every class where you need DI, use autowiring and annotations instead
     *
     * @example function getUser() { return $this->getContainer()->get('User'); }
     *
     * @url http://php-di.org/doc/best-practices.html
     * @return ContainerInterface
     */
    private function getContainer()
    {
        return Container::instance();
    }
}
