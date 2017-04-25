<?php
namespace BetaKiller\Factory;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;

class OrmFactory extends NamespaceBasedFactory
{
    protected function init()
    {
        $this->injectDefinitions($this);
    }

    public function injectDefinitions(NamespaceBasedFactory $factory)
    {
        $factory
            ->setExpectedInterface(OrmInterface::class)
            ->setClassPrefixes('Model');
    }
}
