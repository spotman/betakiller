<?php
declare(strict_types=1);

namespace Spotman\Defence;

class CompositeArgumentDefinition extends AbstractArgumentDefinition implements CompositeArgumentDefinitionInterface
{
    /**
     * @var ArgumentDefinitionInterface[]
     */
    private $children = [];

    /**
     * CompositeArgumentDefinition constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, self::TYPE_COMPOSITE);
    }

    public function addChild(ArgumentDefinitionInterface $argument): void
    {
        $this->children[] = $argument;
    }

    /**
     * @return ArgumentDefinitionInterface[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
