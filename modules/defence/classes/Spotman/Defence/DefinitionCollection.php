<?php
declare(strict_types=1);

namespace Spotman\Defence;

class DefinitionCollection implements DefinitionCollectionInterface
{
    /**
     * @var ArgumentDefinitionInterface[]
     */
    private $arguments = [];

    public function addChild(ArgumentDefinitionInterface $argument): void
    {
        $this->arguments[] = $argument;
    }

    /**
     * @return ArgumentDefinitionInterface[]
     */
    public function getChildren(): array
    {
        return $this->arguments;
    }
}
