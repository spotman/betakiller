<?php
declare(strict_types=1);

namespace Spotman\Defence;

interface CompositeArrayArgumentDefinitionInterface extends ArgumentDefinitionInterface
{
    /**
     * @return \Spotman\Defence\CompositeArgumentDefinitionInterface
     */
    public function getComposite(): CompositeArgumentDefinitionInterface;
}
