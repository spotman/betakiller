<?php
declare(strict_types=1);

namespace Spotman\Defence;

interface DefinitionCollectionInterface
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
