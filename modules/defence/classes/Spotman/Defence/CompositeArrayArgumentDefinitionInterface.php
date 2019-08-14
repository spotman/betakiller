<?php
declare(strict_types=1);

namespace Spotman\Defence;

interface CompositeArrayArgumentDefinitionInterface extends ArgumentDefinitionInterface, ArgumentWithRulesInterface
{
    /**
     * @return \Spotman\Defence\CompositeArgumentDefinitionInterface
     */
    public function getComposite(): CompositeArgumentDefinitionInterface;
}
