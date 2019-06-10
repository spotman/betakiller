<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use Spotman\Defence\DefinitionBuilderInterface;

interface GetRequestActionInterface
{
    /**
     * Arguments definition for request` GET data
     *
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     */
    public function defineGetArguments(DefinitionBuilderInterface $builder): void;
}
