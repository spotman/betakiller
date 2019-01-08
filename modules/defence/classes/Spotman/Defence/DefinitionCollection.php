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
        return \count($this->arguments);
    }
}
