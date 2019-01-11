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

    /**
     * Count elements of an object
     *
     * @link  https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return \count($this->children);
    }
}
