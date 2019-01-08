<?php
declare(strict_types=1);

namespace Spotman\Defence;

interface DefinitionCollectionInterface extends \Countable
{
    /**
     * @param \Spotman\Defence\ArgumentDefinitionInterface $argument
     */
    public function addChild(ArgumentDefinitionInterface $argument): void;

    /**
     * @return ArgumentDefinitionInterface[]
     */
    public function getChildren(): array;
}
