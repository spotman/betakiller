<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

interface NamespaceBasedFactoryBuilderInterface
{
    /**
     * @return \BetaKiller\Factory\NamespaceBasedFactoryInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createFactory(): NamespaceBasedFactoryInterface;
}
