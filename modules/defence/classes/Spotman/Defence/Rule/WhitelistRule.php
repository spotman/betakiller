<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

use Spotman\Defence\ArgumentDefinitionInterface;

class WhitelistRule implements DefinitionRuleInterface
{
    /**
     * @var array
     */
    private $allowed;

    public function __construct(array $allowed)
    {
        if (!$allowed) {
            throw new \InvalidArgumentException('Allowed values must be specified');
        }

        $this->allowed = $allowed;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'whitelist';
    }

    /**
     * @return string[]
     */
    public function getArgumentTypes(): array
    {
        return [
            ArgumentDefinitionInterface::TYPE_INTEGER,
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
        if (!\is_int($value) && !\is_string($value)) {
            throw new \InvalidArgumentException;
        }

        return \in_array($value, $this->allowed, true);
    }
}
