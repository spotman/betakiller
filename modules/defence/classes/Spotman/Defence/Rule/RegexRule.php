<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

use Spotman\Defence\ArgumentDefinitionInterface;

class RegexRule implements DefinitionRuleInterface
{
    /**
     * @var int
     */
    private $pattern;

    /**
     * RegexRule constructor.
     *
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        if (!$pattern) {
            throw new \InvalidArgumentException('Regex pattern is missing');
        }

        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'regex';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_STRING,
        ];
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        if (!\is_string($value)) {
            throw new \InvalidArgumentException;
        }

        return (bool)\preg_match($this->pattern, $value);
    }
}
